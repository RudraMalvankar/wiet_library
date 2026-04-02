<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

mobile_require_method('POST');

$input = mobile_read_json_body();
$action = strtolower(trim((string)($input['action'] ?? 'verify_otp')));

try {
    if ($action === 'verify_otp') {
        $resetToken = trim((string)($input['reset_token'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $otp = trim((string)($input['otp'] ?? ''));

        if ($resetToken === '' || $email === '' || strlen($otp) !== 6) {
            mobile_error('reset_token, email and 6-digit otp are required.', 422);
        }

        $stmt = $pdo->prepare(
            "SELECT ResetID, MemberNo, ExpiresAt, IsUsed, OTP
             FROM PasswordResets
             WHERE ResetToken = :reset_token
             AND Email = :email
             ORDER BY CreatedAt DESC
             LIMIT 1"
        );
        $stmt->execute([
            'reset_token' => $resetToken,
            'email' => $email,
        ]);

        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record || (int)$record['IsUsed'] === 1) {
            mobile_error('Invalid or already used reset token.', 404);
        }

        if (strtotime((string)$record['ExpiresAt']) < time()) {
            mobile_error('OTP expired. Request a new one.', 410);
        }

        if ((string)$record['OTP'] !== $otp) {
            mobile_error('Invalid OTP.', 401);
        }

        mobile_ok([
            'verified' => true,
            'reset_id' => (int)$record['ResetID'],
            'member_no' => (int)$record['MemberNo'],
        ]);
    }

    if ($action === 'reset_password') {
        $resetId = (int)($input['reset_id'] ?? 0);
        $memberNo = (int)($input['member_no'] ?? 0);
        $newPassword = (string)($input['new_password'] ?? '');

        if ($resetId <= 0 || $memberNo <= 0 || strlen($newPassword) < 6) {
            mobile_error('reset_id, member_no and new_password(min 6 chars) are required.', 422);
        }

        $checkStmt = $pdo->prepare(
            "SELECT ResetID, IsUsed, ExpiresAt
             FROM PasswordResets
             WHERE ResetID = :reset_id
             AND MemberNo = :member_no
             LIMIT 1"
        );
        $checkStmt->execute([
            'reset_id' => $resetId,
            'member_no' => $memberNo,
        ]);
        $record = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$record || (int)$record['IsUsed'] === 1) {
            mobile_error('Invalid reset request.', 404);
        }

        if (strtotime((string)$record['ExpiresAt']) < time()) {
            mobile_error('Reset request expired.', 410);
        }

        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);

        $updateStudent = $pdo->prepare('UPDATE Student SET Password = :password WHERE MemberNo = :member_no');
        $updateStudent->execute([
            'password' => $hashed,
            'member_no' => $memberNo,
        ]);

        $markUsed = $pdo->prepare('UPDATE PasswordResets SET IsUsed = 1, UsedAt = NOW() WHERE ResetID = :reset_id');
        $markUsed->execute(['reset_id' => $resetId]);

        mobile_ok([
            'reset' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    mobile_error('Unsupported action.', 422);
} catch (Throwable $e) {
    error_log('Mobile verify otp error: ' . $e->getMessage());
    mobile_error('Unable to process OTP request.', 500);
}
