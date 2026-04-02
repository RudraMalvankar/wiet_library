<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('GET');

try {
    $student = mobile_require_auth($pdo);
    $studentId = (int)$student['StudentID'];
    $memberNo = (int)$student['MemberNo'];

    $profileStmt = $pdo->prepare(
        "SELECT
            s.StudentID,
            s.MemberNo,
            s.FirstName,
            s.MiddleName,
            s.Surname,
            s.DOB,
            s.Gender,
            s.BloodGroup,
            s.Branch,
            s.CourseName,
            s.PRN,
            s.Mobile,
            s.Email,
            s.Address,
            s.ValidTill,
            m.MemberName,
            m.Phone,
            m.Email AS MemberEmail,
            m.AdmissionDate,
            m.Status,
            m.BooksIssued
         FROM Student s
         INNER JOIN Member m ON m.MemberNo = s.MemberNo
         WHERE s.StudentID = :student_id
         LIMIT 1"
    );
    $profileStmt->execute(['student_id' => $studentId]);
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        mobile_error('Profile not found.', 404);
    }

    $statsStmt = $pdo->prepare(
        "SELECT
            COUNT(DISTINCT c.CirculationID) AS total_borrowed,
            COUNT(DISTINCT CASE WHEN c.Status = 'Active' THEN c.CirculationID END) AS current_borrowed,
            COALESCE(SUM(fp.PaidAmount), 0) AS total_fines_paid
         FROM Member m
         LEFT JOIN Circulation c ON c.MemberNo = m.MemberNo
         LEFT JOIN FinePayments fp ON fp.MemberNo = m.MemberNo
         WHERE m.MemberNo = :member_no
         GROUP BY m.MemberNo"
    );
    $statsStmt->execute(['member_no' => $memberNo]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $footfallStmt = $pdo->prepare('SELECT COUNT(*) AS total_visits FROM Footfall WHERE MemberNo = :member_no');
    $footfallStmt->execute(['member_no' => $memberNo]);
    $footfall = $footfallStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $activityStmt = $pdo->prepare(
        "SELECT Action, Details, Timestamp
         FROM ActivityLog
         WHERE UserID = :student_id
         AND UserType = 'Student'
         ORDER BY Timestamp DESC
         LIMIT 10"
    );
    $activityStmt->execute(['student_id' => $studentId]);

    mobile_ok([
        'personal_info' => [
            'full_name' => $profile['MemberName'],
            'student_id' => $profile['PRN'] ?: $profile['StudentID'],
            'email' => $profile['Email'] ?: $profile['MemberEmail'],
            'phone' => $profile['Mobile'] ?: $profile['Phone'],
            'date_of_birth' => $profile['DOB'],
            'gender' => $profile['Gender'] ?: 'N/A',
            'blood_group' => $profile['BloodGroup'] ?: 'N/A',
            'address' => $profile['Address'] ?: 'N/A',
        ],
        'academic_info' => [
            'course' => $profile['CourseName'] ?: 'N/A',
            'branch' => $profile['Branch'] ?: 'N/A',
            'roll_number' => $profile['PRN'] ?: 'N/A',
            'admission_year' => !empty($profile['AdmissionDate']) ? date('Y', strtotime((string)$profile['AdmissionDate'])) : 'N/A',
            'membership_valid_till' => $profile['ValidTill'] ?: 'N/A',
        ],
        'library_stats' => [
            'membership_since' => $profile['AdmissionDate'] ?: 'N/A',
            'total_books_borrowed' => (int)($stats['total_borrowed'] ?? 0),
            'current_borrowed' => (int)($stats['current_borrowed'] ?? 0),
            'total_visits' => (int)($footfall['total_visits'] ?? 0),
            'total_fines_paid' => (float)($stats['total_fines_paid'] ?? 0),
            'books_issued_live' => (int)($profile['BooksIssued'] ?? 0),
        ],
        'recent_activity' => $activityStmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
} catch (Throwable $e) {
    error_log('Mobile profile error: ' . $e->getMessage());
    mobile_error('Unable to load profile.', 500);
}
