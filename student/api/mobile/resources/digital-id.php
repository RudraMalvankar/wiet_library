<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    $student = mobile_require_auth($pdo);
    $studentId = (int)$student['StudentID'];

    $stmt = $pdo->prepare(
        "SELECT
            s.StudentID,
            s.MemberNo,
            s.PRN,
            s.CourseName,
            s.Branch,
            s.ValidTill,
            s.Email,
            s.Mobile,
            s.Photo,
            m.MemberName,
            m.AdmissionDate,
            m.Status,
            m.BooksIssued,
            m.Entitlement
         FROM Student s
         INNER JOIN Member m ON m.MemberNo = s.MemberNo
         WHERE s.StudentID = :student_id
         LIMIT 1"
    );
    $stmt->execute(['student_id' => $studentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        mobile_error('Digital ID not found.', 404);
    }

    mobile_ok([
        'card' => [
            'member_no' => (int)$row['MemberNo'],
            'member_code' => mobile_member_code($row['MemberNo']),
            'student_id' => $row['PRN'] ?: $row['StudentID'],
            'name' => $row['MemberName'],
            'course' => $row['CourseName'] ?: 'N/A',
            'department' => $row['Branch'] ?: 'N/A',
            'issue_date' => $row['AdmissionDate'] ?: null,
            'expiry_date' => $row['ValidTill'] ?: null,
            'status' => $row['Status'] ?: 'Active',
            'barcode' => str_pad((string)$row['MemberNo'], 12, '0', STR_PAD_LEFT),
            'qr_code' => 'MEMBER:' . (string)$row['MemberNo'],
            'email' => $row['Email'] ?: '',
            'mobile' => $row['Mobile'] ?: '',
            'books_issued' => (int)($row['BooksIssued'] ?? 0),
            'entitlement' => $row['Entitlement'] ?: 'Standard',
            'photo_base64' => !empty($row['Photo']) ? base64_encode((string)$row['Photo']) : null,
        ],
    ]);
} catch (Throwable $e) {
    error_log('Mobile digital id error: ' . $e->getMessage());
    mobile_error('Unable to load digital ID.', 500);
}
