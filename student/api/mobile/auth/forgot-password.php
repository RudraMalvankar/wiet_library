<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('POST');

if (!checkRateLimit('mobile_forgot_pw_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 60)) {
    mobile_error('Too many attempts. Please wait and try again.', 429);
}

$input = mobile_read_json_body();
$email = trim((string)($input['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    mobile_error('A valid email is required.', 422);
}

try {
    $stmt = $pdo->prepare(
        "SELECT s.StudentID, s.MemberNo, s.Email, m.MemberName, m.Status
         FROM Student s
         INNER JOIN Member m ON m.MemberNo = s.MemberNo
         WHERE s.Email = :email
         LIMIT 1"
    );
    $stmt->execute(['email' => $email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student || ($student['Status'] ?? '') !== 'Active') {
        mobile_error('No active account found with this email.', 404);
    }

    $otp = sprintf('%06d', random_int(0, 999999));
    $resetToken = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    $insertStmt = $pdo->prepare(
        "INSERT INTO PasswordResets (MemberNo, Email, ResetToken, OTP, ExpiresAt, IPAddress)
         VALUES (:member_no, :email, :reset_token, :otp, :expires_at, :ip)"
    );
    $insertStmt->execute([
        'member_no' => $student['MemberNo'],
        'email' => $email,
        'reset_token' => $resetToken,
        'otp' => $otp,
        'expires_at' => $expiresAt,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    ]);

    mobile_ok([
        'message' => 'OTP generated successfully.',
        'reset_token' => $resetToken,
        'email' => $email,
        'otp' => $otp,
        'expires_at' => $expiresAt,
        'note' => 'OTP is returned for current setup compatibility. Replace with email/SMS delivery in production.',
    ]);
} catch (Throwable $e) {
    error_log('Mobile forgot password error: ' . $e->getMessage());
    mobile_error('Unable to start password reset.', 500);
}
