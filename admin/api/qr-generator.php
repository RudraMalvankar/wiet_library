<?php
/**
 * QR Code Generator API
 * Handles bulk QR generation, printing, and regeneration
 */

// Session MUST start before any output or requires that may output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_once '../../libs/phpqrcode/phpqrcode.php';

// Authentication check (sendJson sets Content-Type header internally)
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
    sendJson(['success' => false, 'message' => 'Unauthorized. Please login.'], 401);
}

$adminId = $_SESSION['admin_id'] ?? $_SESSION['AdminID'] ?? null;

$type   = $_GET['type']   ?? '';
$action = $_GET['action'] ?? '';

// Auto-migrate: ensure QRCode path columns exist with correct types
try {
    // holding table: add QRCode VARCHAR column if missing (it only has QrCodeImg LONGBLOB)
    $holdingCols = $pdo->query("SHOW COLUMNS FROM `holding` LIKE 'QRCode'")->fetchAll();
    if (empty($holdingCols)) {
        $pdo->exec("ALTER TABLE `holding` ADD COLUMN `QRCode` VARCHAR(255) DEFAULT NULL");
    }

    // student table: QRCode exists but as BLOB — modify to VARCHAR(255) for storing paths
    $studentCol = $pdo->query("SHOW COLUMNS FROM `student` LIKE 'QRCode'")->fetch(PDO::FETCH_ASSOC);
    if ($studentCol && stripos($studentCol['Type'], 'blob') !== false) {
        $pdo->exec("ALTER TABLE `student` MODIFY COLUMN `QRCode` VARCHAR(255) DEFAULT NULL");
    } elseif (!$studentCol) {
        $pdo->exec("ALTER TABLE `student` ADD COLUMN `QRCode` VARCHAR(255) DEFAULT NULL");
    }
} catch (PDOException $e) {
    error_log("QR column migration error: " . $e->getMessage());
}

// Ensure storage/qrcodes directory exists
$qrDir = realpath(__DIR__ . '/../../storage') . '/qrcodes';
if (!is_dir($qrDir)) { @mkdir($qrDir, 0775, true); }

/**
 * Generate QR code file and return its absolute filepath (or false on failure)
 */
function generateQRCode($text, $filename, $size = 200) {
    $baseDir = realpath(__DIR__ . '/../../storage');
    if (!$baseDir) {
        $baseDir = __DIR__ . '/../../storage';
    }
    $dir = $baseDir . '/qrcodes';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $filepath = $dir . '/' . $filename;
    try {
        QRcode::png($text, $filepath, QR_ECLEVEL_L, intval($size / 25), 2);
        return file_exists($filepath) ? $filepath : false;
    } catch (Exception $e) {
        error_log("QR Generation Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Read a QR code file and return its base64-encoded content
 */
function qrToBase64($filepath) {
    if ($filepath && file_exists($filepath)) {
        return base64_encode(file_get_contents($filepath));
    }
    return null;
}

try {
    // ── Preview: fetch items with existing QR codes (or generate on-the-fly) ──
    if ($action === 'preview') {
        $labelType = $type; // 'book' or 'member'
        $limit     = 200;
        $items     = [];
        $baseDir   = realpath(__DIR__ . '/../../');

        if ($labelType === 'member') {
            $stmt = $pdo->query(
                "SELECT m.MemberNo, m.MemberName, s.PRN, s.QRCode
                 FROM Member m
                 LEFT JOIN Student s ON m.MemberNo = s.MemberNo
                 WHERE s.QRCode IS NOT NULL AND s.QRCode != ''
                 ORDER BY m.MemberNo
                 LIMIT $limit"
            );
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $filepath = $baseDir . '/' . $row['QRCode'];
                $b64 = qrToBase64($filepath);
                if (!$b64) {
                    $fn = 'member_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $row['MemberNo']) . '.png';
                    $fp = generateQRCode('MEMBER:' . $row['MemberNo'], $fn);
                    if ($fp) {
                        $qp = 'storage/qrcodes/' . $fn;
                        $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?")->execute([$qp, $row['MemberNo']]);
                        $b64 = qrToBase64($fp);
                    }
                }
                $label  = $row['MemberName'] . ($row['PRN'] ? ' (' . $row['PRN'] . ')' : '');
                $items[] = ['code' => $row['MemberNo'], 'name' => $label, 'base64' => $b64];
            }
        } else {
            // Default: books
            $stmt = $pdo->query(
                "SELECT h.AccNo, h.QRCode, b.Title, b.Author1
                 FROM Holding h
                 JOIN Books b ON h.CatNo = b.CatNo
                 WHERE h.QRCode IS NOT NULL AND h.QRCode != ''
                 ORDER BY h.AccNo
                 LIMIT $limit"
            );
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $filepath = $baseDir . '/' . $row['QRCode'];
                $b64 = qrToBase64($filepath);
                if (!$b64) {
                    $fn = 'book_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $row['AccNo']) . '.png';
                    $fp = generateQRCode('BOOK:' . $row['AccNo'], $fn);
                    if ($fp) {
                        $qp = 'storage/qrcodes/' . $fn;
                        $pdo->prepare("UPDATE Holding SET QRCode = ? WHERE AccNo = ?")->execute([$qp, $row['AccNo']]);
                        $b64 = qrToBase64($fp);
                    }
                }
                $label  = $row['Title'] . ($row['Author1'] ? ' - ' . $row['Author1'] : '');
                $items[] = ['code' => $row['AccNo'], 'name' => $label, 'base64' => $b64];
            }
        }

        sendJson(['success' => true, 'count' => count($items), 'items' => $items]);
    }

    // ── Single Book ──────────────────────────────────────────────────────────
    elseif ($type === 'book') {
        $accNo = $_GET['accNo'] ?? '';
        if (!$accNo) sendJson(['success' => false, 'message' => 'AccNo required']);

        $stmt = $pdo->prepare("SELECT h.*, b.Title, b.Author1 FROM Holding h JOIN Books b ON h.CatNo = b.CatNo WHERE h.AccNo = ?");
        $stmt->execute([$accNo]);
        $holding = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$holding) sendJson(['success' => false, 'message' => 'Book not found']);

        $qrData   = 'BOOK:' . $accNo;
        $filename = 'book_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $accNo) . '.png';
        $filepath = generateQRCode($qrData, $filename);

        if ($filepath) {
            $qrPath = 'storage/qrcodes/' . $filename;
            $pdo->prepare("UPDATE Holding SET QRCode = ? WHERE AccNo = ?")->execute([$qrPath, $accNo]);
            $label = $holding['Title'] . ($holding['Author1'] ? ' - ' . $holding['Author1'] : '');
            sendJson([
                'success' => true,
                'message' => 'QR code generated successfully',
                'qrCodes' => [[
                    'code'   => $accNo,
                    'name'   => $label,
                    'image'  => '../' . $qrPath,
                    'base64' => qrToBase64($filepath)
                ]]
            ]);
        } else {
            sendJson(['success' => false, 'message' => 'Failed to generate QR code']);
        }
    }

    // ── Book Range ───────────────────────────────────────────────────────────
    elseif ($type === 'book-range') {
        $start = $_GET['start'] ?? '';
        $end   = $_GET['end']   ?? '';
        if (!$start || !$end) sendJson(['success' => false, 'message' => 'Start and End AccNo required']);

        $stmt = $pdo->prepare("SELECT h.AccNo, b.Title, b.Author1 FROM Holding h JOIN Books b ON h.CatNo = b.CatNo WHERE h.AccNo BETWEEN ? AND ? ORDER BY h.AccNo");
        $stmt->execute([$start, $end]);
        $holdings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0; $qrCodes = [];
        foreach ($holdings as $h) {
            $qrData   = 'BOOK:' . $h['AccNo'];
            $filename = 'book_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $h['AccNo']) . '.png';
            $filepath = generateQRCode($qrData, $filename);
            if ($filepath) {
                $qrPath = 'storage/qrcodes/' . $filename;
                $pdo->prepare("UPDATE Holding SET QRCode = ? WHERE AccNo = ?")->execute([$qrPath, $h['AccNo']]);
                $label = $h['Title'] . ($h['Author1'] ? ' - ' . $h['Author1'] : '');
                $qrCodes[] = ['code' => $h['AccNo'], 'name' => $label, 'image' => '../' . $qrPath, 'base64' => qrToBase64($filepath)];
                $count++;
            }
        }
        sendJson(['success' => true, 'message' => "Generated $count QR codes", 'count' => $count, 'qrCodes' => $qrCodes]);
    }

    // ── All Books (missing QR) ────────────────────────────────────────────────
    elseif ($type === 'book-all') {
        $stmt = $pdo->query("SELECT h.AccNo, b.Title, b.Author1 FROM Holding h JOIN Books b ON h.CatNo = b.CatNo WHERE h.QRCode IS NULL OR h.QRCode = ''");
        $holdings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0; $qrCodes = [];
        foreach ($holdings as $h) {
            $qrData   = 'BOOK:' . $h['AccNo'];
            $filename = 'book_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $h['AccNo']) . '.png';
            $filepath = generateQRCode($qrData, $filename);
            if ($filepath) {
                $qrPath = 'storage/qrcodes/' . $filename;
                $pdo->prepare("UPDATE Holding SET QRCode = ? WHERE AccNo = ?")->execute([$qrPath, $h['AccNo']]);
                $label = $h['Title'] . ($h['Author1'] ? ' - ' . $h['Author1'] : '');
                $qrCodes[] = ['code' => $h['AccNo'], 'name' => $label, 'image' => '../' . $qrPath, 'base64' => qrToBase64($filepath)];
                $count++;
            }
        }
        sendJson(['success' => true, 'message' => "Generated $count QR codes for books", 'count' => $count, 'qrCodes' => $qrCodes]);
    }

    // ── Single Member ────────────────────────────────────────────────────────
    elseif ($type === 'member') {
        $memberNo = $_GET['memberNo'] ?? '';
        if (!$memberNo) sendJson(['success' => false, 'message' => 'memberNo required']);

        $stmt = $pdo->prepare("SELECT m.MemberNo, m.MemberName, s.PRN, s.Branch FROM Member m LEFT JOIN Student s ON m.MemberNo = s.MemberNo WHERE m.MemberNo = ?");
        $stmt->execute([$memberNo]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$member) sendJson(['success' => false, 'message' => 'Member not found']);

        $qrData   = 'MEMBER:' . $memberNo;
        $filename = 'member_' . $memberNo . '.png';
        $filepath = generateQRCode($qrData, $filename);

        if ($filepath) {
            $qrPath = 'storage/qrcodes/' . $filename;
            $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?")->execute([$qrPath, $memberNo]);
            $label = $member['MemberName'] . ($member['PRN'] ? ' (' . $member['PRN'] . ')' : '');
            sendJson([
                'success' => true,
                'message' => 'QR code generated successfully',
                'qrCodes' => [[
                    'code'   => $memberNo,
                    'name'   => $label,
                    'image'  => '../' . $qrPath,
                    'base64' => qrToBase64($filepath)
                ]]
            ]);
        } else {
            sendJson(['success' => false, 'message' => 'Failed to generate QR code']);
        }
    }

    // ── Member Batch (by branch) ──────────────────────────────────────────────
    elseif ($type === 'member-batch') {
        $branch = $_GET['branch'] ?? '';
        $stmt = $pdo->prepare("SELECT m.MemberNo, m.MemberName, s.PRN FROM Member m JOIN Student s ON m.MemberNo = s.MemberNo WHERE s.Branch LIKE ?");
        $stmt->execute(["%$branch%"]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0; $qrCodes = [];
        foreach ($members as $m) {
            $qrData   = 'MEMBER:' . $m['MemberNo'];
            $filename = 'member_' . $m['MemberNo'] . '.png';
            $filepath = generateQRCode($qrData, $filename);
            if ($filepath) {
                $qrPath = 'storage/qrcodes/' . $filename;
                $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?")->execute([$qrPath, $m['MemberNo']]);
                $label = $m['MemberName'] . ($m['PRN'] ? ' (' . $m['PRN'] . ')' : '');
                $qrCodes[] = ['code' => $m['MemberNo'], 'name' => $label, 'image' => '../' . $qrPath, 'base64' => qrToBase64($filepath)];
                $count++;
            }
        }
        sendJson(['success' => true, 'message' => "Generated $count QR codes for branch: $branch", 'count' => $count, 'qrCodes' => $qrCodes]);
    }

    // ── All Members (missing QR) ──────────────────────────────────────────────
    elseif ($type === 'member-all') {
        $stmt = $pdo->query("SELECT m.MemberNo, m.MemberName, s.PRN FROM Member m LEFT JOIN Student s ON m.MemberNo = s.MemberNo WHERE s.QRCode IS NULL OR s.QRCode = ''");
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0; $qrCodes = [];
        foreach ($members as $m) {
            $qrData   = 'MEMBER:' . $m['MemberNo'];
            $filename = 'member_' . $m['MemberNo'] . '.png';
            $filepath = generateQRCode($qrData, $filename);
            if ($filepath) {
                $qrPath = 'storage/qrcodes/' . $filename;
                $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?")->execute([$qrPath, $m['MemberNo']]);
                $label = $m['MemberName'] . ($m['PRN'] ? ' (' . $m['PRN'] . ')' : '');
                $qrCodes[] = ['code' => $m['MemberNo'], 'name' => $label, 'image' => '../' . $qrPath, 'base64' => qrToBase64($filepath)];
                $count++;
            }
        }
        sendJson(['success' => true, 'message' => "Generated $count QR codes for members", 'count' => $count, 'qrCodes' => $qrCodes]);
    }

    // ── Regenerate All Books ──────────────────────────────────────────────────
    elseif ($type === 'regenerate-books') {
        $stmt = $pdo->query("SELECT h.AccNo, b.Title, b.Author1 FROM Holding h JOIN Books b ON h.CatNo = b.CatNo");
        $holdings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0; $qrCodes = [];
        foreach ($holdings as $h) {
            $qrData   = 'BOOK:' . $h['AccNo'];
            $filename = 'book_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $h['AccNo']) . '.png';
            $filepath = generateQRCode($qrData, $filename);
            if ($filepath) {
                $qrPath = 'storage/qrcodes/' . $filename;
                $pdo->prepare("UPDATE Holding SET QRCode = ? WHERE AccNo = ?")->execute([$qrPath, $h['AccNo']]);
                $label = $h['Title'] . ($h['Author1'] ? ' - ' . $h['Author1'] : '');
                $qrCodes[] = ['code' => $h['AccNo'], 'name' => $label, 'image' => '../' . $qrPath, 'base64' => qrToBase64($filepath)];
                $count++;
            }
        }
        sendJson(['success' => true, 'message' => "Regenerated $count book QR codes", 'count' => $count, 'qrCodes' => $qrCodes]);
    }

    // ── Regenerate All Members ────────────────────────────────────────────────
    elseif ($type === 'regenerate-members') {
        $stmt = $pdo->query("SELECT m.MemberNo, m.MemberName, s.PRN FROM Member m LEFT JOIN Student s ON m.MemberNo = s.MemberNo");
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0; $qrCodes = [];
        foreach ($members as $m) {
            $qrData   = 'MEMBER:' . $m['MemberNo'];
            $filename = 'member_' . $m['MemberNo'] . '.png';
            $filepath = generateQRCode($qrData, $filename);
            if ($filepath) {
                $qrPath = 'storage/qrcodes/' . $filename;
                $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?")->execute([$qrPath, $m['MemberNo']]);
                $label = $m['MemberName'] . ($m['PRN'] ? ' (' . $m['PRN'] . ')' : '');
                $qrCodes[] = ['code' => $m['MemberNo'], 'name' => $label, 'image' => '../' . $qrPath, 'base64' => qrToBase64($filepath)];
                $count++;
            }
        }
        sendJson(['success' => true, 'message' => "Regenerated $count member QR codes", 'count' => $count, 'qrCodes' => $qrCodes]);
    }

    // ── Bulk students (from student-management page) ──────────────────────────
    elseif ($type === 'bulk-students') {
        $ids = [];
        if (!empty($_POST['studentIds'])) {
            $ids = $_POST['studentIds'];
        } else {
            $body = json_decode(file_get_contents('php://input'), true);
            $ids  = $body['studentIds'] ?? [];
        }
        if (empty($ids)) sendJson(['success' => false, 'message' => 'No student IDs provided']);

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT m.MemberNo, m.MemberName, s.PRN FROM Member m JOIN Student s ON m.MemberNo = s.MemberNo WHERE s.StudentID IN ($placeholders)");
        $stmt->execute($ids);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0; $qrCodes = [];
        foreach ($members as $m) {
            $qrData   = 'MEMBER:' . $m['MemberNo'];
            $filename = 'member_' . $m['MemberNo'] . '.png';
            $filepath = generateQRCode($qrData, $filename);
            if ($filepath) {
                $qrPath = 'storage/qrcodes/' . $filename;
                $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?")->execute([$qrPath, $m['MemberNo']]);
                $label = $m['MemberName'] . ($m['PRN'] ? ' (' . $m['PRN'] . ')' : '');
                $qrCodes[] = ['code' => $m['MemberNo'], 'name' => $label, 'image' => '../' . $qrPath, 'base64' => qrToBase64($filepath)];
                $count++;
            }
        }
        sendJson(['success' => true, 'message' => "Generated $count QR codes", 'count' => $count, 'qrCodes' => $qrCodes]);
    }

    else {
        sendJson(['success' => false, 'message' => 'Invalid request type: ' . $type]);
    }
    
} catch (PDOException $e) {
    sendJson(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
?>
