<?php
/**
 * Mobile API bootstrap
 * Shared auth, response, and utility helpers for student mobile endpoints.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../includes/functions.php';

function mobile_json(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function mobile_ok(array $data = []): void
{
    mobile_json([
        'success' => true,
        'data' => $data,
    ]);
}

function mobile_error(string $message, int $statusCode = 400, array $extra = []): void
{
    mobile_json([
        'success' => false,
        'message' => $message,
        'error' => $extra,
    ], $statusCode);
}

function mobile_require_method(string $method): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
        mobile_error('Method not allowed', 405);
    }
}

function mobile_read_json_body(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        mobile_error('Invalid JSON payload', 400);
    }

    return $decoded;
}

function mobile_get_bearer_token(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
    if (!$header && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (preg_match('/Bearer\s+(.+)/i', (string)$header, $matches)) {
        return trim($matches[1]);
    }

    return null;
}

function mobile_ensure_sessions_table(PDO $pdo): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS MobileSessions (
            SessionID INT AUTO_INCREMENT PRIMARY KEY,
            StudentID INT NOT NULL,
            MemberNo INT NOT NULL,
            Token VARCHAR(128) NOT NULL UNIQUE,
            DeviceInfo VARCHAR(255) NULL,
            ExpiresAt DATETIME NOT NULL,
            LastSeenAt DATETIME NOT NULL,
            IsActive TINYINT(1) NOT NULL DEFAULT 1,
            CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_mobile_token (Token),
            INDEX idx_mobile_student (StudentID),
            INDEX idx_mobile_member (MemberNo),
            INDEX idx_mobile_expires (ExpiresAt)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $ensured = true;
}

function mobile_issue_token(PDO $pdo, array $student): array
{
    mobile_ensure_sessions_table($pdo);

    $token = bin2hex(random_bytes(32));
    $expiresAt = (new DateTime('+8 hours'))->format('Y-m-d H:i:s');
    $deviceInfo = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'mobile-app'), 0, 255);

    $stmt = $pdo->prepare(
        'INSERT INTO MobileSessions (StudentID, MemberNo, Token, DeviceInfo, ExpiresAt, LastSeenAt, IsActive)
         VALUES (:student_id, :member_no, :token, :device_info, :expires_at, NOW(), 1)'
    );

    $stmt->execute([
        'student_id' => $student['StudentID'],
        'member_no' => $student['MemberNo'],
        'token' => $token,
        'device_info' => $deviceInfo,
        'expires_at' => $expiresAt,
    ]);

    return [
        'token' => $token,
        'expires_at' => $expiresAt,
    ];
}

function mobile_require_auth(PDO $pdo): array
{
    mobile_ensure_sessions_table($pdo);

    $token = mobile_get_bearer_token();
    if (!$token) {
        mobile_error('Missing authorization token', 401);
    }

    $stmt = $pdo->prepare(
        "SELECT
            ms.SessionID,
            ms.Token,
            ms.ExpiresAt,
            s.StudentID,
            s.MemberNo,
            s.Email,
            s.Branch,
            s.CourseName,
            s.PRN,
            s.Mobile,
            s.ValidTill,
            m.MemberName,
            m.Status,
            m.BooksIssued
         FROM MobileSessions ms
         INNER JOIN Student s ON s.StudentID = ms.StudentID
         INNER JOIN Member m ON m.MemberNo = ms.MemberNo
         WHERE ms.Token = :token
         AND ms.IsActive = 1
         AND ms.ExpiresAt > NOW()
         AND m.Status = 'Active'
         LIMIT 1"
    );

    $stmt->execute(['token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        mobile_error('Session expired. Please login again.', 401);
    }

    $touch = $pdo->prepare(
        'UPDATE MobileSessions
         SET LastSeenAt = NOW(), ExpiresAt = DATE_ADD(NOW(), INTERVAL 8 HOUR)
         WHERE SessionID = :session_id'
    );
    $touch->execute(['session_id' => $row['SessionID']]);

    return $row;
}

function mobile_member_code($memberNo): string
{
    return 'M' . str_pad((string)$memberNo, 7, '0', STR_PAD_LEFT);
}

function mobile_build_student_payload(array $row): array
{
    return [
        'student_id' => (int)$row['StudentID'],
        'member_no' => (int)$row['MemberNo'],
        'member_code' => mobile_member_code($row['MemberNo']),
        'name' => $row['MemberName'],
        'email' => $row['Email'] ?? '',
        'branch' => $row['Branch'] ?? '',
        'course' => $row['CourseName'] ?? '',
        'prn' => $row['PRN'] ?? '',
        'mobile' => $row['Mobile'] ?? '',
        'books_issued' => (int)($row['BooksIssued'] ?? 0),
    ];
}
