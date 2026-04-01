<?php
/**
 * Bulk Import API
 * Supports preview + mapping + import for Books and Students data.
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized. Please login.'], 401);
}

$identifier = $_SESSION['AdminID'] ?? $_SESSION['admin_id'] ?? $_SERVER['REMOTE_ADDR'];
if (!checkRateLimit($identifier, 120, 60)) {
    jsonResponse(['success' => false, 'message' => 'Rate limit exceeded. Please try again later.'], 429);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'metadata':
            if ($method !== 'GET') {
                jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            $configs = getImportConfigs($pdo);
            jsonResponse(['success' => true, 'data' => $configs]);
            break;

        case 'history':
            if ($method !== 'GET') {
                jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            $history = $_SESSION['bulk_import_history'] ?? [];
            jsonResponse(['success' => true, 'data' => $history]);
            break;

        case 'preview':
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            $importType = strtolower(trim($_POST['import_type'] ?? ''));
            $configs = getImportConfigs($pdo);
            if (!isset($configs[$importType])) {
                jsonResponse(['success' => false, 'message' => 'Invalid import type selected.'], 400);
            }

            if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                jsonResponse(['success' => false, 'message' => 'Please upload a valid file.'], 400);
            }

            $parsed = parseUploadedTableFile($_FILES['file']['tmp_name'], $_FILES['file']['name']);
            $suggestedMapping = suggestMapping($parsed['headers'], $configs[$importType]);

            jsonResponse([
                'success' => true,
                'data' => [
                    'file_name' => $_FILES['file']['name'],
                    'headers' => $parsed['headers'],
                    'total_rows' => count($parsed['rows']),
                    'sample_rows' => array_slice($parsed['rows'], 0, 5),
                    'suggested_mapping' => $suggestedMapping,
                    'import_config' => $configs[$importType]
                ]
            ]);
            break;

        case 'import':
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            $importType = strtolower(trim($_POST['import_type'] ?? ''));
            $mappingJson = $_POST['mapping'] ?? '';
            $mapping = json_decode($mappingJson, true);

            if (!is_array($mapping)) {
                jsonResponse(['success' => false, 'message' => 'Invalid mapping payload.'], 400);
            }

            $configs = getImportConfigs($pdo);
            if (!isset($configs[$importType])) {
                jsonResponse(['success' => false, 'message' => 'Invalid import type selected.'], 400);
            }

            if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                jsonResponse(['success' => false, 'message' => 'Please upload a valid file.'], 400);
            }

            $parsed = parseUploadedTableFile($_FILES['file']['tmp_name'], $_FILES['file']['name']);
            $config = $configs[$importType];

            $missingRequired = [];
            foreach ($config['fields'] as $field) {
                if (!empty($field['required'])) {
                    $mappedHeader = trim((string)($mapping[$field['key']] ?? ''));
                    if ($mappedHeader === '') {
                        $missingRequired[] = $field['label'];
                    }
                }
            }

            if (!empty($missingRequired)) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Please map all required fields before import.',
                    'missing_required_fields' => $missingRequired
                ], 400);
            }

            $result = ($importType === 'books')
                ? importBooks($pdo, $parsed['rows'], $mapping, $config)
                : importStudents($pdo, $parsed['rows'], $mapping, $config);

            addImportHistory([
                'id' => time(),
                'importType' => ucfirst($importType),
                'fileName' => $_FILES['file']['name'],
                'totalRows' => $result['summary']['total_rows'],
                'added' => $result['summary']['added_count'],
                'skipped' => $result['summary']['skipped_count'],
                'errors' => $result['summary']['error_count'],
                'importedBy' => $_SESSION['admin_name'] ?? $_SESSION['Name'] ?? 'Admin',
                'importDate' => date('Y-m-d H:i:s')
            ]);

            jsonResponse(['success' => true, 'data' => $result]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Unknown action.'], 404);
    }
} catch (Throwable $e) {
    error_log('Bulk import API error: ' . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'Bulk import failed: ' . $e->getMessage()
    ], 500);
}

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function addImportHistory(array $entry): void
{
    if (!isset($_SESSION['bulk_import_history']) || !is_array($_SESSION['bulk_import_history'])) {
        $_SESSION['bulk_import_history'] = [];
    }

    array_unshift($_SESSION['bulk_import_history'], $entry);
    $_SESSION['bulk_import_history'] = array_slice($_SESSION['bulk_import_history'], 0, 30);
}

function getImportConfigs(PDO $pdo): array
{
    $bookRequired = ['Title', 'Author1', 'ISBN', 'Publisher', 'Subject'];
    $studentRequired = [];

    $bookExclude = ['CatNo', 'DateAdded', 'CreatedBy'];
    $memberExclude = ['DateAdded', 'BooksIssued'];
    $studentExclude = ['StudentID', 'Photo', 'QRCode'];

    $bookFields = [];
    foreach (getTableColumns($pdo, 'Books') as $col) {
        if (in_array($col, $bookExclude, true)) {
            continue;
        }

        $bookFields[] = [
            'key' => 'books.' . $col,
            'table' => 'Books',
            'column' => $col,
            'label' => $col,
            'required' => in_array($col, $bookRequired, true)
        ];
    }

    $studentFields = [];
    foreach (getTableColumns($pdo, 'Member') as $col) {
        if (in_array($col, $memberExclude, true)) {
            continue;
        }

        $studentFields[] = [
            'key' => 'member.' . $col,
            'table' => 'Member',
            'column' => $col,
            'label' => 'Member.' . $col,
            'required' => in_array('member.' . $col, $studentRequired, true)
        ];
    }

    foreach (getTableColumns($pdo, 'Student') as $col) {
        if (in_array($col, $studentExclude, true)) {
            continue;
        }

        $studentFields[] = [
            'key' => 'student.' . $col,
            'table' => 'Student',
            'column' => $col,
            'label' => 'Student.' . $col,
            'required' => in_array('student.' . $col, $studentRequired, true)
        ];
    }

    return [
        'books' => [
            'type' => 'books',
            'title' => 'Books Import',
            'description' => 'Import book catalog records into Books table.',
            'fields' => $bookFields,
            'max_records' => 5000
        ],
        'students' => [
            'type' => 'students',
            'title' => 'Students Import',
            'description' => 'Import student/member records into Member and Student tables.',
            'fields' => $studentFields,
            'max_records' => 5000
        ]
    ];
}

function getTableColumns(PDO $pdo, string $table): array
{
    $stmt = $pdo->query('DESCRIBE `' . $table . '`');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map(static function ($row) {
        return $row['Field'];
    }, $rows);
}

function parseUploadedTableFile(string $tmpPath, string $originalName): array
{
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($extension === 'csv') {
        $rows = parseCsvFile($tmpPath);
    } elseif ($extension === 'xlsx') {
        $rows = parseXlsxFile($tmpPath);
    } elseif ($extension === 'xls') {
        throw new RuntimeException('Legacy .xls files are not supported. Please save as .xlsx or .csv and try again.');
    } else {
        throw new RuntimeException('Unsupported file type. Please upload .csv or .xlsx file.');
    }

    if (empty($rows)) {
        throw new RuntimeException('Uploaded file is empty.');
    }

    $headerRowIndex = null;
    foreach ($rows as $index => $row) {
        if (!isRowEmpty($row)) {
            $headerRowIndex = $index;
            break;
        }
    }

    if ($headerRowIndex === null) {
        throw new RuntimeException('No header row found in file.');
    }

    $headers = buildUniqueHeaders($rows[$headerRowIndex]);
    $assocRows = [];

    for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        if (isRowEmpty($row)) {
            continue;
        }

        $assoc = [];
        foreach ($headers as $colIndex => $header) {
            $value = $row[$colIndex] ?? '';
            $assoc[$header] = is_string($value) ? trim($value) : $value;
        }
        $assocRows[] = $assoc;
    }

    return [
        'headers' => array_values($headers),
        'rows' => $assocRows
    ];
}

function parseCsvFile(string $filePath): array
{
    $rows = [];
    $handle = fopen($filePath, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Unable to read CSV file.');
    }

    while (($data = fgetcsv($handle)) !== false) {
        $rows[] = array_map(static function ($value) {
            $value = (string)$value;
            return preg_replace('/^\xEF\xBB\xBF/', '', $value);
        }, $data);
    }
    fclose($handle);

    return $rows;
}

function parseXlsxFile(string $filePath): array
{
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        throw new RuntimeException('Unable to open XLSX file.');
    }

    $sharedStrings = [];
    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml !== false) {
        $shared = @simplexml_load_string($sharedXml);
        if ($shared !== false) {
            foreach ($shared->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $run) {
                        $text .= (string)$run->t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }
    }

    $sheetPath = null;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (strpos($name, 'xl/worksheets/sheet') === 0 && substr($name, -4) === '.xml') {
            $sheetPath = $name;
            break;
        }
    }

    if ($sheetPath === null) {
        $zip->close();
        throw new RuntimeException('No worksheet found in XLSX file.');
    }

    $sheetXml = $zip->getFromName($sheetPath);
    $zip->close();

    if ($sheetXml === false) {
        throw new RuntimeException('Unable to read worksheet from XLSX file.');
    }

    $sheet = @simplexml_load_string($sheetXml);
    if ($sheet === false || !isset($sheet->sheetData)) {
        throw new RuntimeException('Invalid worksheet XML in XLSX file.');
    }

    $rows = [];
    foreach ($sheet->sheetData->row as $rowNode) {
        $row = [];
        foreach ($rowNode->c as $cell) {
            $ref = (string)$cell['r'];
            $colLetters = preg_replace('/\d+/', '', $ref);
            $colIndex = excelColumnToIndex($colLetters);

            $type = (string)$cell['t'];
            $value = '';

            if ($type === 's') {
                $idx = (int)$cell->v;
                $value = $sharedStrings[$idx] ?? '';
            } elseif ($type === 'inlineStr') {
                $value = (string)$cell->is->t;
            } else {
                $value = isset($cell->v) ? (string)$cell->v : '';
            }

            $row[$colIndex] = trim((string)$value);
        }

        if (!empty($row)) {
            ksort($row);
            $max = max(array_keys($row));
            $normalized = [];
            for ($i = 0; $i <= $max; $i++) {
                $normalized[$i] = $row[$i] ?? '';
            }
            $rows[] = $normalized;
        }
    }

    return $rows;
}

function excelColumnToIndex(string $letters): int
{
    $letters = strtoupper($letters);
    $index = 0;
    $len = strlen($letters);

    for ($i = 0; $i < $len; $i++) {
        $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
    }

    return max(0, $index - 1);
}

function isRowEmpty(array $row): bool
{
    foreach ($row as $value) {
        if (trim((string)$value) !== '') {
            return false;
        }
    }
    return true;
}

function buildUniqueHeaders(array $headerRow): array
{
    $headers = [];
    $seen = [];

    foreach ($headerRow as $index => $value) {
        $base = trim((string)$value);
        $base = preg_replace('/^\xEF\xBB\xBF/', '', $base);
        if ($base === '') {
            $base = 'Column' . ($index + 1);
        }

        $candidate = $base;
        $suffix = 2;
        while (isset($seen[strtolower($candidate)])) {
            $candidate = $base . '_' . $suffix;
            $suffix++;
        }

        $seen[strtolower($candidate)] = true;
        $headers[$index] = $candidate;
    }

    return $headers;
}

function suggestMapping(array $headers, array $config): array
{
    $mapping = [];
    $canonicalHeaders = [];
    foreach ($headers as $header) {
        $canonicalHeaders[canonical($header)] = $header;
    }

    foreach ($config['fields'] as $field) {
        $mapping[$field['key']] = '';

        $targetName = canonical($field['column']);
        if (isset($canonicalHeaders[$targetName])) {
            $mapping[$field['key']] = $canonicalHeaders[$targetName];
            continue;
        }

        foreach (getHeaderSynonyms($field['key'], $field['column']) as $synonym) {
            $c = canonical($synonym);
            if (isset($canonicalHeaders[$c])) {
                $mapping[$field['key']] = $canonicalHeaders[$c];
                break;
            }
        }
    }

    return $mapping;
}

function getHeaderSynonyms(string $key, string $column): array
{
    $map = [
        'books.Title' => ['Book Title', 'Title Name', 'BookName'],
        'books.Author1' => ['Author', 'Primary Author', 'Main Author'],
        'books.ISBN' => ['ISBN Number', 'ISBN-13'],
        'books.Publisher' => ['Publication', 'Published By'],
        'books.Subject' => ['Category', 'Topic', 'Discipline'],

        'member.MemberNo' => ['Membership No', 'Member Number', 'Member No', 'MemberNo', 'ID', 'Student ID', 'hip No'],
        'member.MemberName' => ['Name', 'Student Name', 'Full Name'],
        'member.Group' => ['Group', 'Member Group', 'Type'],
        'member.Phone' => ['Phone', 'Mobile', 'Phone Number'],
        'member.Email' => ['Email', 'Email Address'],

        'student.PRN' => ['PRN Number', 'Registration Number', 'Roll Number', 'SR no.', 'SR No', 'Sr No', 'Serial No'],
        'student.Branch' => ['Department', 'Branch Name', 'Course Name', 'Course'],
        'student.FirstName' => ['First Name'],
        'student.MiddleName' => ['Middle Name'],
        'student.Surname' => ['Last Name', 'Surname'],
        'student.Mobile' => ['Mobile', 'Mobile Number', 'Phone'],
        'student.Email' => ['Student Email', 'Email Address'],
        'student.DOB' => ['Date of Birth', 'Birth Date']
    ];

    return $map[$key] ?? [$column];
}

function canonical(string $value): string
{
    return strtolower(preg_replace('/[^a-z0-9]/', '', $value));
}

function pickValueByHeaderAliases(array $row, array $aliases): ?string
{
    if (empty($row)) {
        return null;
    }

    $byCanonical = [];
    foreach ($row as $header => $value) {
        $byCanonical[canonical((string)$header)] = $value;
    }

    foreach ($aliases as $alias) {
        $key = canonical($alias);
        if (!array_key_exists($key, $byCanonical)) {
            continue;
        }

        $value = trim((string)$byCanonical[$key]);
        if ($value !== '') {
            return $value;
        }
    }

    return null;
}

function importBooks(PDO $pdo, array $rows, array $mapping, array $config): array
{
    $fieldsByKey = [];
    foreach ($config['fields'] as $f) {
        $fieldsByKey[$f['key']] = $f;
    }

    $insertColumns = [];
    foreach ($mapping as $fieldKey => $header) {
        if (trim((string)$header) === '') {
            continue;
        }
        if (!isset($fieldsByKey[$fieldKey])) {
            continue;
        }
        $insertColumns[] = $fieldsByKey[$fieldKey]['column'];
    }

    $insertColumns = array_values(array_unique($insertColumns));
    if (empty($insertColumns)) {
        throw new RuntimeException('No mapped columns found for import.');
    }

    $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
    $columnSql = implode(', ', array_map(static function ($col) {
        return '`' . $col . '`';
    }, $insertColumns));

    $insertStmt = $pdo->prepare('INSERT INTO Books (' . $columnSql . ') VALUES (' . $placeholders . ')');
    $dupByIsbn = $pdo->prepare('SELECT CatNo FROM Books WHERE ISBN = ? LIMIT 1');
    $dupByTitleAuthor = $pdo->prepare('SELECT CatNo FROM Books WHERE Title = ? AND COALESCE(Author1, "") = ? LIMIT 1');

    $added = [];
    $skipped = [];
    $errors = [];

    foreach ($rows as $index => $row) {
        $rowNo = $index + 2;

        $rowData = mapRowByFieldKey($row, $mapping);
        $title = trim((string)($rowData['books.Title'] ?? ''));
        $author1 = trim((string)($rowData['books.Author1'] ?? ''));
        $isbn = trim((string)($rowData['books.ISBN'] ?? ''));

        if ($title === '') {
            $errors[] = [
                'row' => $rowNo,
                'reason' => 'Missing required Title value.',
                'identifier' => ''
            ];
            continue;
        }

        if ($isbn !== '') {
            $dupByIsbn->execute([$isbn]);
            if ($dupByIsbn->fetch(PDO::FETCH_ASSOC)) {
                $skipped[] = [
                    'row' => $rowNo,
                    'reason' => 'Duplicate ISBN already exists.',
                    'identifier' => $isbn
                ];
                continue;
            }
        } elseif ($author1 !== '') {
            $dupByTitleAuthor->execute([$title, $author1]);
            if ($dupByTitleAuthor->fetch(PDO::FETCH_ASSOC)) {
                $skipped[] = [
                    'row' => $rowNo,
                    'reason' => 'Duplicate book (Title + Author1) already exists.',
                    'identifier' => $title
                ];
                continue;
            }
        }

        $params = [];
        foreach ($insertColumns as $col) {
            $fieldKey = 'books.' . $col;
            $params[] = normalizeColumnValue($col, $rowData[$fieldKey] ?? null);
        }

        try {
            $insertStmt->execute($params);
            $added[] = [
                'row' => $rowNo,
                'identifier' => $title,
                'created_id' => (int)$pdo->lastInsertId()
            ];
        } catch (Throwable $e) {
            $errors[] = [
                'row' => $rowNo,
                'reason' => $e->getMessage(),
                'identifier' => $title
            ];
        }
    }

    return [
        'summary' => [
            'total_rows' => count($rows),
            'added_count' => count($added),
            'skipped_count' => count($skipped),
            'error_count' => count($errors)
        ],
        'added' => $added,
        'skipped' => $skipped,
        'errors' => $errors
    ];
}

function importStudents(PDO $pdo, array $rows, array $mapping, array $config): array
{
    $fieldsByKey = [];
    foreach ($config['fields'] as $f) {
        $fieldsByKey[$f['key']] = $f;
    }

    $memberColumns = [];
    $studentColumns = [];

    foreach ($mapping as $fieldKey => $header) {
        if (trim((string)$header) === '') {
            continue;
        }
        if (!isset($fieldsByKey[$fieldKey])) {
            continue;
        }

        if (strpos($fieldKey, 'member.') === 0) {
            $memberColumns[] = $fieldsByKey[$fieldKey]['column'];
        } elseif (strpos($fieldKey, 'student.') === 0) {
            $studentColumns[] = $fieldsByKey[$fieldKey]['column'];
        }
    }

    $memberColumns = array_values(array_unique($memberColumns));
    $studentColumns = array_values(array_unique($studentColumns));

    if (!in_array('MemberNo', $memberColumns, true)) {
        $memberColumns[] = 'MemberNo';
    }
    if (!in_array('MemberName', $memberColumns, true)) {
        $memberColumns[] = 'MemberName';
    }
    if (!in_array('Group', $memberColumns, true)) {
        $memberColumns[] = 'Group';
    }
    if (!in_array('Status', $memberColumns, true)) {
        $memberColumns[] = 'Status';
    }
    if (!in_array('AdmissionDate', $memberColumns, true)) {
        $memberColumns[] = 'AdmissionDate';
    }

    $studentColumns = array_values(array_filter($studentColumns, static function ($col) {
        return $col !== 'MemberNo';
    }));

    if (!in_array('PRN', $studentColumns, true)) {
        $studentColumns[] = 'PRN';
    }
    if (!in_array('Branch', $studentColumns, true)) {
        $studentColumns[] = 'Branch';
    }

    $memberSqlCols = implode(', ', array_map(static function ($col) {
        return '`' . $col . '`';
    }, $memberColumns));
    $memberPlaceholders = implode(', ', array_fill(0, count($memberColumns), '?'));
    $insertMember = $pdo->prepare('INSERT INTO Member (' . $memberSqlCols . ') VALUES (' . $memberPlaceholders . ')');

    $memberUpdateColumns = array_values(array_filter($memberColumns, static function ($col) {
        return $col !== 'MemberNo';
    }));
    $updateMember = null;
    if (!empty($memberUpdateColumns)) {
        $memberSetSql = implode(', ', array_map(static function ($col) {
            return '`' . $col . '` = ?';
        }, $memberUpdateColumns));
        $updateMember = $pdo->prepare('UPDATE Member SET ' . $memberSetSql . ' WHERE MemberNo = ?');
    }

    $studentAllColumns = array_merge(['MemberNo'], $studentColumns);
    $studentSqlCols = implode(', ', array_map(static function ($col) {
        return '`' . $col . '`';
    }, $studentAllColumns));
    $studentPlaceholders = implode(', ', array_fill(0, count($studentAllColumns), '?'));
    $insertStudent = $pdo->prepare('INSERT INTO Student (' . $studentSqlCols . ') VALUES (' . $studentPlaceholders . ')');

    $studentSetSql = implode(', ', array_map(static function ($col) {
        return '`' . $col . '` = ?';
    }, $studentColumns));
    $updateStudent = $pdo->prepare('UPDATE Student SET ' . $studentSetSql . ' WHERE MemberNo = ?');

    $memberExistsStmt = $pdo->prepare('SELECT MemberNo FROM Member WHERE MemberNo = ? LIMIT 1');
    $prnExistsStmt = $pdo->prepare('SELECT MemberNo FROM Student WHERE PRN = ? LIMIT 1');
    $studentByMemberStmt = $pdo->prepare('SELECT MemberNo, PRN FROM Student WHERE MemberNo = ? LIMIT 1');

    $added = [];
    $skipped = [];
    $errors = [];

    foreach ($rows as $index => $row) {
        $rowNo = $index + 2;
        $rowData = mapRowByFieldKey($row, $mapping);

        $memberNo = trim((string)($rowData['member.MemberNo'] ?? ''));
        if ($memberNo === '') {
            $memberNo = (string)(pickValueByHeaderAliases($row, [
                'Membership No', 'Member No', 'MemberNo', 'MembershipNo', 'ID', 'hip No'
            ]) ?? '');
        }
        $memberName = trim((string)($rowData['member.MemberName'] ?? ''));
        if ($memberName === '') {
            $memberName = (string)(pickValueByHeaderAliases($row, ['Name', 'Student Name', 'Full Name']) ?? '');
        }
        $firstName = trim((string)($rowData['student.FirstName'] ?? ''));
        if ($firstName === '') {
            $firstName = (string)(pickValueByHeaderAliases($row, ['First Name', 'Firstname']) ?? '');
        }
        $middleName = trim((string)($rowData['student.MiddleName'] ?? ''));
        if ($middleName === '') {
            $middleName = (string)(pickValueByHeaderAliases($row, ['Middle Name', 'Middlename']) ?? '');
        }
        $surname = trim((string)($rowData['student.Surname'] ?? ''));
        if ($surname === '') {
            $surname = (string)(pickValueByHeaderAliases($row, ['Surname', 'Last Name', 'Lastname']) ?? '');
        }
        $rowData['student.FirstName'] = $firstName;
        $rowData['student.MiddleName'] = $middleName;
        $rowData['student.Surname'] = $surname;
        if ($memberName === '') {
            $nameParts = array_values(array_filter([
                preg_replace('/\s+/', ' ', $firstName),
                preg_replace('/\s+/', ' ', $middleName),
                preg_replace('/\s+/', ' ', $surname)
            ], static function ($part) {
                return trim((string)$part) !== '';
            }));
            if (!empty($nameParts)) {
                $memberName = implode(' ', $nameParts);
            }
        }
        $rowData['member.MemberName'] = $memberName;
        $prn = trim((string)($rowData['student.PRN'] ?? ''));
        if ($prn === '') {
            $prn = (string)(pickValueByHeaderAliases($row, ['SR no.', 'SR No', 'Sr No', 'Serial No', 'Roll No', 'Roll Number']) ?? '');
        }
        if ($prn === '') {
            $prn = $memberNo;
        }
        $rowData['student.PRN'] = $prn;
        $branch = trim((string)($rowData['student.Branch'] ?? ''));
        if ($branch === '') {
            $branch = (string)(pickValueByHeaderAliases($row, ['Branch', 'Department', 'Course Name', 'Course']) ?? '');
        }
        $rowData['student.Branch'] = $branch;

        if ($memberNo === '' || $memberName === '') {
            $errors[] = [
                'row' => $rowNo,
                'reason' => 'Missing one of required values: MemberNo, MemberName.',
                'identifier' => $memberName !== '' ? $memberName : $memberNo
            ];
            continue;
        }

        $memberValues = [];
        foreach ($memberColumns as $col) {
            $fieldKey = 'member.' . $col;
            $value = $rowData[$fieldKey] ?? null;

            if ($col === 'Group' && trim((string)$value) === '') {
                $value = 'Student';
            }
            if ($col === 'Status' && trim((string)$value) === '') {
                $value = 'Active';
            }
            if ($col === 'AdmissionDate' && trim((string)$value) === '') {
                $value = date('Y-m-d');
            }

            $memberValues[$col] = normalizeColumnValue($col, $value);
        }

        $studentValues = [];
        foreach ($studentColumns as $col) {
            $fieldKey = 'student.' . $col;
            $value = $rowData[$fieldKey] ?? null;

            if ($col === 'Branch' && trim((string)$value) === '') {
                $value = $branch;
            }
            if ($col === 'PRN' && trim((string)$value) === '') {
                $value = $prn;
            }
            if ($col === 'Mobile' && trim((string)$value) === '') {
                $value = $rowData['member.Phone'] ?? null;
            }
            if ($col === 'Email' && trim((string)$value) === '') {
                $value = $rowData['member.Email'] ?? null;
            }

            $studentValues[$col] = normalizeColumnValue($col, $value);
        }

        $memberInsertParams = [];
        foreach ($memberColumns as $col) {
            $memberInsertParams[] = $memberValues[$col] ?? null;
        }

        $studentInsertParams = [normalizeColumnValue('MemberNo', $memberNo)];
        foreach ($studentColumns as $col) {
            $studentInsertParams[] = $studentValues[$col] ?? null;
        }

        try {
            $pdo->beginTransaction();

            $memberExistsStmt->execute([$memberNo]);
            $memberExists = $memberExistsStmt->fetch(PDO::FETCH_ASSOC);

            if ($memberExists) {
                if ($updateMember !== null) {
                    $memberUpdateParams = [];
                    foreach ($memberUpdateColumns as $col) {
                        $memberUpdateParams[] = $memberValues[$col] ?? null;
                    }
                    $memberUpdateParams[] = $memberNo;
                    $updateMember->execute($memberUpdateParams);
                }
            } else {
                $insertMember->execute($memberInsertParams);
            }

            if ($prn !== '') {
                $prnExistsStmt->execute([$prn]);
                $prnOwner = $prnExistsStmt->fetch(PDO::FETCH_ASSOC);
                if ($prnOwner && (string)$prnOwner['MemberNo'] !== (string)$memberNo) {
                    throw new RuntimeException('PRN already belongs to another member.');
                }
            }

            $studentByMemberStmt->execute([$memberNo]);
            $studentExisting = $studentByMemberStmt->fetch(PDO::FETCH_ASSOC);
            $action = 'inserted';

            if ($studentExisting) {
                $studentUpdateParams = [];
                foreach ($studentColumns as $col) {
                    $studentUpdateParams[] = $studentValues[$col] ?? null;
                }
                $studentUpdateParams[] = $memberNo;
                $updateStudent->execute($studentUpdateParams);
                $action = 'updated';
            } else {
                $insertStudent->execute($studentInsertParams);
            }

            $pdo->commit();

            $added[] = [
                'row' => $rowNo,
                'identifier' => $memberNo . ' / ' . $prn,
                'created_id' => $memberNo,
                'action' => $action
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = [
                'row' => $rowNo,
                'reason' => $e->getMessage(),
                'identifier' => $memberNo
            ];
        }
    }

    return [
        'summary' => [
            'total_rows' => count($rows),
            'added_count' => count($added),
            'skipped_count' => count($skipped),
            'error_count' => count($errors)
        ],
        'added' => $added,
        'skipped' => $skipped,
        'errors' => $errors
    ];
}

function mapRowByFieldKey(array $row, array $mapping): array
{
    $mapped = [];
    foreach ($mapping as $fieldKey => $header) {
        $header = trim((string)$header);
        if ($header === '') {
            $mapped[$fieldKey] = null;
            continue;
        }
        $mapped[$fieldKey] = $row[$header] ?? null;
    }
    return $mapped;
}

function normalizeColumnValue(string $column, $value)
{
    if ($value === null) {
        return null;
    }

    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $intCols = ['Year'];
    $decimalCols = ['ItemPrice', 'ItemCost', 'FinePerDay'];
    $dateCols = ['BillDate', 'AdmissionDate', 'ClosingDate', 'DOB', 'ValidTill'];

    if (in_array($column, $intCols, true)) {
        return (int)$value;
    }

    if (in_array($column, $decimalCols, true)) {
        return (float)$value;
    }

    if (in_array($column, $dateCols, true)) {
        return normalizeDateValue($value);
    }

    return $value;
}

function normalizeDateValue(string $value): ?string
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value;
    }

    if (is_numeric($value)) {
        $excelDate = (float)$value;
        if ($excelDate > 0) {
            $unix = (int)(($excelDate - 25569) * 86400);
            if ($unix > 0) {
                return gmdate('Y-m-d', $unix);
            }
        }
    }

    $ts = strtotime($value);
    return $ts !== false ? date('Y-m-d', $ts) : null;
}
