<?php
/**
 * QR Code Generator API
 * Handles bulk QR generation, printing, and regeneration
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_once '../../libs/phpqrcode/phpqrcode.php';

header('Content-Type: application/json');

session_start();
$adminId = $_SESSION['admin_id'] ?? $_SESSION['AdminID'] ?? 1;

$type = $_GET['type'] ?? '';
$action = $_GET['action'] ?? '';

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
        QRcode::png($text, $filepath, QR_ECLEVEL_L, $size/25, 2);
        return true;
    } catch (Exception $e) {
        error_log("QR Generation Error: " . $e->getMessage());
        return false;
    }
}

try {
    // Book QR Code Generation
    if ($type === 'book') {
        $accNo = $_GET['accNo'] ?? '';
        
        $stmt = $pdo->prepare("SELECT h.*, b.Title FROM Holding h JOIN Books b ON h.CatNo = b.CatNo WHERE h.AccNo = ?");
        $stmt->execute([$accNo]);
        $holding = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$holding) {
            sendJson(['success' => false, 'message' => 'Book not found']);
        }
        
        $qrData = "BOOK:" . $accNo;
        $filename = 'book_' . $accNo . '.png';
        
        if (generateQRCode($qrData, $filename)) {
            $qrPath = 'storage/qrcodes/' . $filename;
            
            $stmt = $pdo->prepare("UPDATE Holding SET QRCode = ? WHERE AccNo = ?");
            $stmt->execute([$qrPath, $accNo]);
            
            sendJson([
                'success' => true,
                'message' => 'QR code generated successfully',
                'qrCodes' => [[
                    'code' => $accNo,
                    'name' => $holding['Title'],
                    'image' => '../' . $qrPath
                ]]
            ]);
        } else {
            sendJson(['success' => false, 'message' => 'Failed to generate QR code']);
        }
    }
    
    // Book Range QR Generation
    elseif ($type === 'book-range') {
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        
        $stmt = $pdo->prepare("SELECT h.AccNo, b.Title FROM Holding h JOIN Books b ON h.CatNo = b.CatNo WHERE h.AccNo BETWEEN ? AND ? ORDER BY h.AccNo");
        $stmt->execute([$start, $end]);
        $holdings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        $qrCodes = [];
        
        foreach ($holdings as $holding) {
            $qrData = "BOOK:" . $holding['AccNo'];
            $filename = 'book_' . $holding['AccNo'] . '.png';
            
            if (generateQRCode($qrData, $filename)) {
                $qrPath = 'storage/qrcodes/' . $filename;
                
                $stmt = $pdo->prepare("UPDATE Holding SET QRCode = ? WHERE AccNo = ?");
                $stmt->execute([$qrPath, $holding['AccNo']]);
                
                $qrCodes[] = [
                    'code' => $holding['AccNo'],
                    'name' => $holding['Title'],
                    'image' => '../' . $qrPath
                ];
                $count++;
            }
        }
        
        sendJson([
            'success' => true,
            'message' => "Generated $count QR codes",
            'count' => $count,
            'qrCodes' => array_slice($qrCodes, 0, 10) // Show first 10
        ]);
    }
    
    // All Books QR Generation
    elseif ($type === 'book-all') {
        $stmt = $pdo->query("SELECT h.AccNo, b.Title FROM Holding h JOIN Books b ON h.CatNo = b.CatNo WHERE h.QRCode IS NULL OR h.QRCode = ''");
        $holdings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        foreach ($holdings as $holding) {
            $qrData = "BOOK:" . $holding['AccNo'];
            $filename = 'book_' . $holding['AccNo'] . '.png';
            
            if (generateQRCode($qrData, $filename)) {
                $qrPath = 'storage/qrcodes/' . $filename;
                
                $stmt = $pdo->prepare("UPDATE Holding SET QRCode = ? WHERE AccNo = ?");
                $stmt->execute([$qrPath, $holding['AccNo']]);
                $count++;
            }
        }
        
        sendJson([
            'success' => true,
            'message' => "Generated $count QR codes for books",
            'count' => $count
        ]);
    }
    
    // Member QR Code Generation
    elseif ($type === 'member') {
        $memberNo = $_GET['memberNo'] ?? '';
        
        $stmt = $pdo->prepare("SELECT m.*, s.PRN FROM Member m LEFT JOIN Student s ON m.MemberNo = s.MemberNo WHERE m.MemberNo = ?");
        $stmt->execute([$memberNo]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$member) {
            sendJson(['success' => false, 'message' => 'Member not found']);
        }
        
        $qrData = "MEMBER:" . $memberNo;
        $filename = 'member_' . $memberNo . '.png';
        
        if (generateQRCode($qrData, $filename)) {
            $qrPath = 'storage/qrcodes/' . $filename;
            
            $stmt = $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?");
            $stmt->execute([$qrPath, $memberNo]);
            
            sendJson([
                'success' => true,
                'message' => 'QR code generated successfully',
                'qrCodes' => [[
                    'code' => $memberNo,
                    'name' => $member['MemberName'],
                    'image' => '../' . $qrPath
                ]]
            ]);
        } else {
            sendJson(['success' => false, 'message' => 'Failed to generate QR code']);
        }
    }
    
    // Member Batch QR Generation
    elseif ($type === 'member-batch') {
        $branch = $_GET['branch'] ?? '';
        
        $stmt = $pdo->prepare("SELECT m.MemberNo, m.MemberName FROM Member m JOIN Student s ON m.MemberNo = s.MemberNo WHERE s.Branch LIKE ?");
        $stmt->execute(["%$branch%"]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        foreach ($members as $member) {
            $qrData = "MEMBER:" . $member['MemberNo'];
            $filename = 'member_' . $member['MemberNo'] . '.png';
            
            if (generateQRCode($qrData, $filename)) {
                $qrPath = 'storage/qrcodes/' . $filename;
                
                $stmt = $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?");
                $stmt->execute([$qrPath, $member['MemberNo']]);
                $count++;
            }
        }
        
        sendJson([
            'success' => true,
            'message' => "Generated $count QR codes for branch: $branch",
            'count' => $count
        ]);
    }
    
    // All Members QR Generation
    elseif ($type === 'member-all') {
        $stmt = $pdo->query("SELECT m.MemberNo, m.MemberName FROM Member m LEFT JOIN Student s ON m.MemberNo = s.MemberNo WHERE s.QRCode IS NULL OR s.QRCode = ''");
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        foreach ($members as $member) {
            $qrData = "MEMBER:" . $member['MemberNo'];
            $filename = 'member_' . $member['MemberNo'] . '.png';
            
            if (generateQRCode($qrData, $filename)) {
                $qrPath = 'storage/qrcodes/' . $filename;
                
                $stmt = $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?");
                $stmt->execute([$qrPath, $member['MemberNo']]);
                $count++;
            }
        }
        
        sendJson([
            'success' => true,
            'message' => "Generated $count QR codes for members",
            'count' => $count
        ]);
    }
    
    // Bulk operations
    elseif ($type === 'books' || $type === 'regenerate-books') {
        $condition = ($type === 'regenerate-books') ? "1=1" : "(h.QRCode IS NULL OR h.QRCode = '')";
        
        $stmt = $pdo->query("SELECT h.AccNo FROM Holding h WHERE $condition");
        $holdings = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $count = 0;
        foreach ($holdings as $accNo) {
            $qrData = "BOOK:" . $accNo;
            $filename = 'book_' . $accNo . '.png';
            
            if (generateQRCode($qrData, $filename)) {
                $qrPath = 'storage/qrcodes/' . $filename;
                
                $stmt = $pdo->prepare("UPDATE Holding SET QRCode = ? WHERE AccNo = ?");
                $stmt->execute([$qrPath, $accNo]);
                $count++;
            }
        }
        
        sendJson([
            'success' => true,
            'message' => "Generated $count QR codes",
            'count' => $count
        ]);
    }
    
    elseif ($type === 'members' || $type === 'regenerate-members') {
        $condition = ($type === 'regenerate-members') ? "1=1" : "(s.QRCode IS NULL OR s.QRCode = '')";
        
        $stmt = $pdo->query("SELECT m.MemberNo FROM Member m LEFT JOIN Student s ON m.MemberNo = s.MemberNo WHERE $condition");
        $members = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $count = 0;
        foreach ($members as $memberNo) {
            $qrData = "MEMBER:" . $memberNo;
            $filename = 'member_' . $memberNo . '.png';
            
            if (generateQRCode($qrData, $filename)) {
                $qrPath = 'storage/qrcodes/' . $filename;
                
                $stmt = $pdo->prepare("UPDATE Student SET QRCode = ? WHERE MemberNo = ?");
                $stmt->execute([$qrPath, $memberNo]);
                $count++;
            }
        }
        
        sendJson([
            'success' => true,
            'message' => "Generated $count QR codes",
            'count' => $count
        ]);
    }
    
    else {
        sendJson(['success' => false, 'message' => 'Invalid request type']);
    }
    
} catch (PDOException $e) {
    sendJson(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
?>
