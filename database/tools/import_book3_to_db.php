<?php
/**
 * Import unique Book3 student records into Member and Student tables.
 *
 * Usage:
 *   php database/tools/import_book3_to_db.php
 */

require_once __DIR__ . '/../../includes/db_connect.php';

$book3Path = __DIR__ . '/../clg-dataset/Book3.md';

if (!file_exists($book3Path)) {
	fwrite(STDERR, "ERROR: Book3 file not found at {$book3Path}" . PHP_EOL);
	exit(1);
}

function parseDate(?string $value): ?string {
	$value = trim((string)$value);
	if ($value === '') {
		return null;
	}

	$dt = DateTime::createFromFormat('d/m/Y', $value);
	if ($dt instanceof DateTime) {
		return $dt->format('Y-m-d');
	}

	$dt = DateTime::createFromFormat('Y-m-d', $value);
	if ($dt instanceof DateTime) {
		return $dt->format('Y-m-d');
	}

	return null;
}

function normalizeMemberNo(string $membershipNo): ?int {
	$digits = preg_replace('/\D+/', '', $membershipNo);
	if ($digits === null || $digits === '') {
		return null;
	}
	return (int)$digits;
}

function buildMemberName(string $first, string $middle, string $surname): string {
	return trim(preg_replace('/\s+/', ' ', trim($first . ' ' . $middle . ' ' . $surname)) ?? '');
}

function parseBook3Rows(string $path): array {
	$lines = file($path, FILE_IGNORE_NEW_LINES);
	if ($lines === false) {
		throw new RuntimeException('Unable to read Book3 file.');
	}

	$rows = [];
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || $line[0] !== '|') {
			continue;
		}

		$cells = array_map('trim', explode('|', trim($line, '|')));
		if (count($cells) < 15) {
			continue;
		}

		if (!preg_match('/^\d+$/', $cells[0])) {
			continue;
		}

		$rows[] = [
			'membership_no' => $cells[1],
			'course_name' => $cells[2],
			'card_colour' => $cells[3],
			'surname' => $cells[4],
			'middle_name' => $cells[5],
			'first_name' => $cells[6],
			'group_name' => $cells[7],
			'designation' => $cells[8],
			'address' => $cells[9],
			'email' => $cells[10],
			'mobile' => $cells[11],
			'gender' => $cells[12],
			'admission_date' => $cells[13],
			'closing_date' => $cells[14],
		];
	}

	return $rows;
}

function getStudentColumns(PDO $pdo): array {
	$stmt = $pdo->query('SHOW COLUMNS FROM Student');
	$columns = [];
	foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
		$columns[$col['Field']] = true;
	}
	return $columns;
}

try {
	$rows = parseBook3Rows($book3Path);
	if (count($rows) === 0) {
		throw new RuntimeException('No data rows found in Book3.md');
	}

	$studentColumns = getStudentColumns($pdo);

	$checkMemberStmt = $pdo->prepare('SELECT MemberNo FROM Member WHERE MemberNo = ?');
	$insertMemberStmt = $pdo->prepare(
		'INSERT INTO Member (MemberNo, MemberName, `Group`, Designation, Phone, Email, AdmissionDate, ClosingDate, Status)
		 VALUES (?, ?, ?, ?, ?, ?, ?, ?, "Active")'
	);

	$checkStudentByPrnStmt = $pdo->prepare('SELECT StudentID FROM Student WHERE PRN = ? LIMIT 1');
	$checkStudentByMemberStmt = $pdo->prepare('SELECT StudentID FROM Student WHERE MemberNo = ? LIMIT 1');

	$insertedMembers = 0;
	$insertedStudents = 0;
	$skippedRows = 0;

	$pdo->beginTransaction();

	foreach ($rows as $row) {
		$prn = trim((string)$row['membership_no']);
		$memberNo = normalizeMemberNo($prn);

		if ($memberNo === null || $prn === '') {
			$skippedRows++;
			continue;
		}

		$first = trim((string)$row['first_name']);
		$middle = trim((string)$row['middle_name']);
		$surname = trim((string)$row['surname']);

		$memberName = buildMemberName($first, $middle, $surname);
		if ($memberName === '') {
			$memberName = $prn;
		}

		$groupName = trim((string)$row['group_name']) !== '' ? trim((string)$row['group_name']) : 'Student';
		$designation = trim((string)$row['designation']);
		$mobile = trim((string)$row['mobile']);
		$email = trim((string)$row['email']);
		$admissionDate = parseDate((string)$row['admission_date']);
		$closingDate = parseDate((string)$row['closing_date']);
		$courseName = trim((string)$row['course_name']);

		$checkMemberStmt->execute([$memberNo]);
		if (!$checkMemberStmt->fetch(PDO::FETCH_ASSOC)) {
			$insertMemberStmt->execute([
				$memberNo,
				$memberName,
				$groupName,
				$designation !== '' ? $designation : null,
				$mobile !== '' ? $mobile : null,
				$email !== '' ? $email : null,
				$admissionDate,
				$closingDate,
			]);
			$insertedMembers++;
		}

		$checkStudentByPrnStmt->execute([$prn]);
		$studentExists = $checkStudentByPrnStmt->fetch(PDO::FETCH_ASSOC);
		if (!$studentExists) {
			$checkStudentByMemberStmt->execute([$memberNo]);
			$studentExists = $checkStudentByMemberStmt->fetch(PDO::FETCH_ASSOC);
		}

		if ($studentExists) {
			continue;
		}

		$studentData = [
			'MemberNo' => $memberNo,
			'Surname' => $surname !== '' ? $surname : null,
			'MiddleName' => $middle !== '' ? $middle : null,
			'FirstName' => $first !== '' ? $first : null,
			'Gender' => trim((string)$row['gender']) !== '' ? trim((string)$row['gender']) : null,
			'Branch' => $courseName !== '' ? $courseName : null,
			'CourseName' => $courseName !== '' ? $courseName : null,
			'PRN' => $prn,
			'Mobile' => $mobile !== '' ? $mobile : null,
			'Email' => $email !== '' ? $email : null,
			'Address' => trim((string)$row['address']) !== '' ? trim((string)$row['address']) : null,
			'CardColour' => trim((string)$row['card_colour']) !== '' ? trim((string)$row['card_colour']) : null,
			'ValidTill' => $closingDate,
			'Password' => password_hash('123456', PASSWORD_BCRYPT),
		];

		$insertCols = [];
		$insertVals = [];
		$placeholders = [];

		foreach ($studentData as $col => $val) {
			if (isset($studentColumns[$col])) {
				$insertCols[] = "`{$col}`";
				$insertVals[] = $val;
				$placeholders[] = '?';
			}
		}

		$insertSql = 'INSERT INTO Student (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', $placeholders) . ')';
		$insertStudentStmt = $pdo->prepare($insertSql);
		$insertStudentStmt->execute($insertVals);
		$insertedStudents++;
	}

	$pdo->commit();

	echo 'Book3 import completed.' . PHP_EOL;
	echo 'Rows parsed: ' . count($rows) . PHP_EOL;
	echo 'Members inserted: ' . $insertedMembers . PHP_EOL;
	echo 'Students inserted: ' . $insertedStudents . PHP_EOL;
	echo 'Rows skipped: ' . $skippedRows . PHP_EOL;
	exit(0);
} catch (Throwable $e) {
	if ($pdo instanceof PDO && $pdo->inTransaction()) {
		$pdo->rollBack();
	}

	fwrite(STDERR, 'Import failed: ' . $e->getMessage() . PHP_EOL);
	exit(1);
}

