<?php
/**
 * Books API Endpoints
 * Handles CRUD operations for books and holdings
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Helper: generate QR file and return filename; uses phpqrcode if available
function generate_qr_file($text) {
    $baseDir = realpath(__DIR__ . '/../../storage');
    if (!$baseDir) { $baseDir = __DIR__ . '/../../storage'; }
    $dir = $baseDir . '/qrcodes';
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$text);
    $filename = $dir . '/qr_' . $safe . '.png';
    // Prefer phpqrcode
    $phpqrcode = __DIR__ . '/../../libs/phpqrcode/phpqrcode.php';
    if (file_exists($phpqrcode)) {
        try {
            require_once $phpqrcode;
            QRcode::png($text, $filename, 'L', 4, 2);
            return $filename;
        } catch (Exception $e) {
            // fallthrough to fallback
        }
    }
    // Fallback simple generator
    if (function_exists('imagecreatetruecolor')) {
        $size = 320;
        $img = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, $size, $size, $white);
        $hash = md5((string)$text, true);
        $grid = 21;
        $cell = (int)floor(($size - 20) / $grid);
        $offset = 10;
        $bitIndex = 0;
        for ($y = 0; $y < $grid; $y++) {
            for ($x = 0; $x < $grid; $x++) {
                $byte = ord($hash[(int)floor($bitIndex / 8)] ?? "\0");
                $bit = ($byte >> ($bitIndex % 8)) & 1;
                if ($bit) {
                    imagefilledrectangle(
                        $img,
                        $offset + $x * $cell,
                        $offset + $y * $cell,
                        $offset + ($x + 1) * $cell - 2,
                        $offset + ($y + 1) * $cell - 2,
                        $black
                    );
                }
                $bitIndex++;
            }
        }
        if (function_exists('imagestring')) {
            imagestring($img, 3, 10, $size - 18, (string)$text, $black);
        }
        imagepng($img, $filename);
        imagedestroy($img);
        return $filename;
    }
    // last resort
    $txt = $filename . '.txt';
    file_put_contents($txt, (string)$text);
    return $txt;
}

// Helper: generate QR PNG binary and return raw bytes (no filesystem)
function generate_qr_png($text) {
    // Prefer phpqrcode if available and can output to stdout
    $phpqrcode = __DIR__ . '/../../libs/phpqrcode/phpqrcode.php';
    if (file_exists($phpqrcode)) {
        try {
            ob_start();
            require_once $phpqrcode;
            // QRcode::png outputs PNG when second param is false
            QRcode::png((string)$text, false, 'L', 4, 2);
            $png = ob_get_clean();
            if ($png !== false && strlen($png) > 0) {
                return $png;
            }
        } catch (Exception $e) {
            if (ob_get_level()) { @ob_end_clean(); }
        }
    }

    // Fallback: use GD to draw a pseudo-QR and return binary
    if (function_exists('imagecreatetruecolor')) {
        $size = 320;
        $img = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, $size, $size, $white);
        $hash = md5((string)$text, true);
        $grid = 21;
        $cell = (int)floor(($size - 20) / $grid);
        $offset = 10;
        $bitIndex = 0;
        for ($y = 0; $y < $grid; $y++) {
            for ($x = 0; $x < $grid; $x++) {
                $byte = ord($hash[(int)floor($bitIndex / 8)] ?? "\0");
                $bit = ($byte >> ($bitIndex % 8)) & 1;
                if ($bit) {
                    imagefilledrectangle(
                        $img,
                        $offset + $x * $cell,
                        $offset + $y * $cell,
                        $offset + ($x + 1) * $cell - 2,
                        $offset + ($y + 1) * $cell - 2,
                        $black
                    );
                }
                $bitIndex++;
            }
        }
        if (function_exists('imagestring')) {
            imagestring($img, 3, 10, $size - 18, (string)$text, $black);
        }
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);
        return $png;
    }

    return null;
}

// Quick debug entry log
file_put_contents(__DIR__ . '/api_debug.log', "\n[ENTRY] " . date('c') . " REQUEST: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n", FILE_APPEND);

session_start();

// Debugging helper: capture any unexpected output or fatal errors to a log
ob_start();
register_shutdown_function(function() {
    $logFile = __DIR__ . '/api_debug.log';
    $output = ob_get_contents();
    $err = error_get_last();
    $data = "\n=== API DEBUG " . date('c') . " ===\n";
    $data .= "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
    $data .= "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '') . "\n";
    $data .= "OUTPUT:\n" . $output . "\n";
    if ($err) {
        $data .= "ERROR:\n" . print_r($err, true) . "\n";
    }
    file_put_contents($logFile, $data, FILE_APPEND);
});

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Get all books with holding information, with pagination
            $search = $_GET['search'] ?? '';
            $subject = $_GET['subject'] ?? '';
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $pageSize = isset($_GET['pageSize']) ? max(1, intval($_GET['pageSize'])) : 20;
            $offset = ($page - 1) * $pageSize;

            // Count total books for pagination
            $countSql = "SELECT COUNT(DISTINCT b.CatNo) as total FROM Books b WHERE 1=1";
            $countParams = [];
            if ($search) {
                $countSql .= " AND (b.Title LIKE ? OR b.Author1 LIKE ? OR b.ISBN LIKE ?)";
                $searchTerm = "%{$search}%";
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
            }
            if ($subject) {
                $countSql .= " AND b.Subject = ?";
                $countParams[] = $subject;
            }
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetchColumn();

            // Main query with pagination
            $sql = "
                SELECT b.*, 
                       COUNT(h.HoldID) as TotalCopies,
                       SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) as AvailableCopies,
                       SUM(CASE WHEN h.Status = 'Issued' THEN 1 ELSE 0 END) as IssuedCopies
                FROM Books b
                LEFT JOIN Holding h ON b.CatNo = h.CatNo
                WHERE 1=1
            ";
            $params = [];
            if ($search) {
                $sql .= " AND (b.Title LIKE ? OR b.Author1 LIKE ? OR b.ISBN LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            if ($subject) {
                $sql .= " AND b.Subject = ?";
                $params[] = $subject;
            }
            $sql .= " GROUP BY b.CatNo ORDER BY b.Title LIMIT ? OFFSET ?";
            $params[] = $pageSize;
            $params[] = $offset;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $books = $stmt->fetchAll();
            // Remove or sanitize any binary image data before returning JSON
            foreach ($books as &$b) {
                if (isset($b['QrCodeImg']) && $b['QrCodeImg'] !== null) {
                    // Remove large binary blob from list response to keep JSON safe and small
                    unset($b['QrCodeImg']);
                }
                if (isset($b['Barcode']) && is_resource($b['Barcode'])) {
                    unset($b['Barcode']);
                }
            }
            sendJson(['success' => true, 'data' => $books, 'total' => (int)$total, 'page' => $page, 'pageSize' => $pageSize]);
            break;
            
        case 'get':
            // Get single book details with all holdings
            $catNo = $_GET['catNo'] ?? 0;

            // Get book details
            $stmt = $pdo->prepare("SELECT * FROM Books WHERE CatNo = ?");
            $stmt->execute([$catNo]);
            $book = $stmt->fetch();

            if (!$book) {
                sendJson(['success' => false, 'message' => 'Book not found'], 404);
            }

            // Get all holdings for this book
            $stmt = $pdo->prepare("SELECT * FROM Holding WHERE CatNo = ? ORDER BY AccNo");
            $stmt->execute([$catNo]);
            $holdings = $stmt->fetchAll();
            // Convert any binary QR blob per holding to base64 for JSON
            foreach ($holdings as &$h) {
                if (isset($h['QrCodeImg']) && $h['QrCodeImg'] !== null) {
                    $h['QrCodeBase64'] = base64_encode($h['QrCodeImg']);
                    unset($h['QrCodeImg']);
                }
            }
            $book['holdings'] = $holdings;

            // If there is binary image data for book-level QR/Barcode, return it as base64
            if (isset($book['QrCodeImg']) && $book['QrCodeImg'] !== null) {
                $book['QrCodeBase64'] = base64_encode($book['QrCodeImg']);
                unset($book['QrCodeImg']);
            }
            if (isset($book['Barcode']) && $book['Barcode'] !== null && !is_string($book['Barcode'])) {
                $book['BarcodeBase64'] = base64_encode($book['Barcode']);
                unset($book['Barcode']);
            }

            sendJson(['success' => true, 'data' => $book]);
            break;

        case 'add':
            // Add new book and holdings (accepts JSON or form-encoded/FormData)
                if ($method !== 'POST') {
                    sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
                }

                // Accept JSON body or fall back to form-encoded POST
                $raw = file_get_contents('php://input');
                $data = json_decode($raw, true);
                if (empty($data) && !empty($_POST)) {
                    $data = $_POST;
                }
                if (empty($data) && !empty($_REQUEST)) {
                    $data = $_REQUEST;
                }

                if (empty($data['Title'])) {
                    sendJson(['success' => false, 'message' => 'Title is required'], 400);
                }

                $adminId = $_SESSION['AdminID'] ?? null;

                $pdo->beginTransaction();

                // Insert book
                $stmt = $pdo->prepare("
                    INSERT INTO Books (Title, SubTitle, Author1, Author2, Author3, Publisher, 
                                      Place, Year, Edition, Vol, Pages, ISBN, Subject, Language, 
                                      DocumentType, CreatedBy)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $data['Title'],
                    $data['SubTitle'] ?? null,
                    $data['Author1'] ?? null,
                    $data['Author2'] ?? null,
                    $data['Author3'] ?? null,
                    $data['Publisher'] ?? null,
                    $data['Place'] ?? null,
                    $data['Year'] ?? null,
                    $data['Edition'] ?? null,
                    $data['Vol'] ?? null,
                    $data['Pages'] ?? null,
                    $data['ISBN'] ?? null,
                    $data['Subject'] ?? null,
                    $data['Language'] ?? 'English',
                    $data['DocumentType'] ?? 'BK',
                    $adminId
                ]);

                $catNo = $pdo->lastInsertId();

                // If Holdings table doesn't have QRCode column, add it (safe check)
                try {
                    $colCheck = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Holding' AND COLUMN_NAME = 'QRCode'");
                    $colCheck->execute();
                    $hasCol = $colCheck->fetch();
                    if (!$hasCol) {
                        $pdo->exec("ALTER TABLE Holding ADD COLUMN QRCode VARCHAR(255) NULL AFTER Collection");
                    }
                } catch (Exception $e) {
                    // Non-fatal: if ALTER fails (permissions), continue without storing path in DB
                }

                // Helper to generate QR PNG binary (in-memory) using generate_qr_png()
                $generateQrBinary = function($text) {
                    return generate_qr_png($text);
                };

                // Insert holdings if provided
                if (!empty($data['holdings']) && is_array($data['holdings'])) {
                    $insertSql = "INSERT INTO Holding (AccNo, CatNo, AccDate, ClassNo, BookNo, Status, Location, Section, Collection, QRCode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($insertSql);

                    foreach ($data['holdings'] as $idx => $holding) {
                        // Determine AccNo (use provided or generate)
                        $accNo = $holding['AccNo'] ?? null;
                        if (empty($accNo)) {
                            // Try to generate using helper in includes/functions.php
                            if (function_exists('generateAccNo')) {
                                $accNo = generateAccNo($catNo, $idx + 1);
                            } else {
                                $accNo = $catNo . '-' . str_pad(($idx + 1), 3, '0', STR_PAD_LEFT);
                            }
                        }

                        $accDate = $holding['AccDate'] ?? date('Y-m-d');
                        $classNo = $holding['ClassNo'] ?? null;
                        $bookNo = $holding['BookNo'] ?? null;
                        $status = 'Available';
                        $location = $holding['Location'] ?? null;
                        $section = $holding['Section'] ?? null;
                        $collection = $holding['Collection'] ?? null;

                        // Generate QR binary and insert as BLOB if available
                        $qrBinary = $generateQrBinary($accNo);

                        // Attempt to store blob when column exists, else store null path
                        $qrPath = null;
                        if ($qrBinary !== null) {
                            try {
                                // Check for QrCodeImg column
                                $colCheck2 = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Holding' AND COLUMN_NAME = 'QrCodeImg'");
                                $colCheck2->execute();
                                $hasBlobCol = $colCheck2->fetch();
                                if ($hasBlobCol) {
                                    $ins = $pdo->prepare("INSERT INTO Holding (AccNo, CatNo, AccDate, ClassNo, BookNo, Status, Location, Section, Collection, QrCodeImg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                    $ins->bindParam(1, $accNo);
                                    $ins->bindParam(2, $catNo);
                                    $ins->bindParam(3, $accDate);
                                    $ins->bindParam(4, $classNo);
                                    $ins->bindParam(5, $bookNo);
                                    $ins->bindParam(6, $status);
                                    $ins->bindParam(7, $location);
                                    $ins->bindParam(8, $section);
                                    $ins->bindParam(9, $collection);
                                    $ins->bindParam(10, $qrBinary, PDO::PARAM_LOB);
                                    $ins->execute();
                                    // We've inserted including blob; skip the later insert
                                    continue;
                                }
                            } catch (Exception $e) {
                                // ignore and fall back to path storage
                            }
                        }

                        // Fallback: insert record without blob (store null QRCode path)
                        $stmt->execute([
                            $accNo,
                            $catNo,
                            $accDate,
                            $classNo,
                            $bookNo,
                            $status,
                            $location,
                            $section,
                            $collection,
                            $qrPath
                        ]);
                    }
                }

                $pdo->commit();

                // Activity and Audit logs
                logActivity($pdo, $adminId, 'BOOK_ADD', "Added book: {$data['Title']} (CatNo: {$catNo})");
                logAudit($pdo, $adminId, 'BOOK_ADD', 'Books', $catNo, [
                    'Title' => $data['Title'],
                    'CatNo' => (int)$catNo
                ]);

                sendJson([
                    'success' => true,
                    'message' => 'Book added successfully',
                    'catNo' => $catNo
                ]);
                break;

            case 'lookup':
                // Lookup holding and book details by Accession Number (AccNo)
                $accNo = $_GET['accNo'] ?? '';
                if (!$accNo) {
                    sendJson(['success' => false, 'message' => 'accNo is required'], 400);
                }
                // Use helper to fetch holding with book details
                $holding = getHoldingByAccNo($pdo, $accNo);
                if (!$holding) {
                    sendJson(['success' => false, 'message' => 'Accession not found'], 404);
                }
                // If QRCode is a path, attempt to return a web-relative path
                if (!empty($holding['QRCode']) && is_string($holding['QRCode'])) {
                    $subjectPath = realpath($holding['QRCode']);
                    if ($subjectPath === false) { $subjectPath = $holding['QRCode']; }
                    $holding['QRCodePath'] = str_replace('\\', '/', preg_replace('#^' . preg_quote(realpath(__DIR__ . '/../../'), '#') . '#', '', $subjectPath));
                }
                sendJson(['success' => true, 'data' => $holding]);
                break;

        case 'generate-qr':
            // Generate QR for a holding (by AccNo) or for a CatNo+copy index
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            // Accept JSON or form data
            $raw = file_get_contents('php://input');
            $payload = json_decode($raw, true);
            if (empty($payload) && !empty($_POST)) $payload = $_POST;

            $accNo = $payload['AccNo'] ?? null;
            $catNo = $payload['CatNo'] ?? null;
            $copyIndex = $payload['CopyIndex'] ?? null;

            if (!$accNo) {
                if ($catNo && $copyIndex) {
                    if (function_exists('generateAccNo')) {
                        $accNo = generateAccNo($catNo, $copyIndex);
                    } else {
                        $accNo = $catNo . '-' . str_pad($copyIndex, 3, '0', STR_PAD_LEFT);
                    }
                } else {
                    sendJson(['success' => false, 'message' => 'AccNo or (CatNo and CopyIndex) required'], 400);
                }
            }

            // Generate QR binary in-memory and store into DB (no filesystem dependency)
            $png = generate_qr_png($accNo);
            $base64 = $png ? base64_encode($png) : null;

            // Try to update Holding record and write blob
            $stmt = $pdo->prepare("SELECT * FROM Holding WHERE AccNo = ?");
            $stmt->execute([$accNo]);
            $holding = $stmt->fetch();
            $blobWritten = false;
            if ($holding && $png !== null) {
                try {
                    $colCheck = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Holding' AND COLUMN_NAME = 'QrCodeImg'");
                    $colCheck->execute();
                    $hasCol = $colCheck->fetch();
                    if ($hasCol) {
                        $upd = $pdo->prepare("UPDATE Holding SET QrCodeImg = ? WHERE AccNo = ?");
                        $upd->bindParam(1, $png, PDO::PARAM_LOB);
                        $upd->bindParam(2, $accNo, PDO::PARAM_STR);
                        $upd->execute();
                        $blobWritten = true;
                    }
                } catch (Exception $e) {
                    // ignore blob write failures
                }
            }

            $adminId = $_SESSION['AdminID'] ?? null;
            logAudit($pdo, $adminId, 'QR_GENERATE', 'Holding', $accNo, [
                'blobWritten' => $blobWritten,
                'hasPng' => $png !== null
            ]);

            sendJson(['success' => true, 'AccNo' => $accNo, 'qrBase64' => $base64]);
            break;

        case 'qr':
            // Stream QR binary for download by AccNo (or CatNo+CopyIndex)
            $accNo = $_GET['accNo'] ?? null;
            $catNo = $_GET['catNo'] ?? null;
            $copyIndex = $_GET['copyIndex'] ?? null;
            if (!$accNo) {
                if ($catNo && $copyIndex) {
                    if (function_exists('generateAccNo')) {
                        $accNo = generateAccNo($catNo, $copyIndex);
                    } else {
                        $accNo = $catNo . '-' . str_pad($copyIndex, 3, '0', STR_PAD_LEFT);
                    }
                }
            }
            if (!$accNo) {
                sendJson(['success' => false, 'message' => 'accNo is required'], 400);
            }

            // Try to fetch blob from DB
            $stmt = $pdo->prepare("SELECT QrCodeImg, QRCode FROM Holding WHERE AccNo = ?");
            $stmt->execute([$accNo]);
            $row = $stmt->fetch();
            if ($row) {
                if (!empty($row['QrCodeImg'])) {
                    // Stream binary
                    $adminId = $_SESSION['AdminID'] ?? null;
                    logAudit($pdo, $adminId, 'QR_DOWNLOAD', 'Holding', $accNo, ['source' => 'blob']);
                    header_remove();
                    header('Content-Type: image/png');
                    header('Content-Disposition: attachment; filename="qr_' . basename($accNo) . '.png"');
                    echo $row['QrCodeImg'];
                    exit;
                }
                // Fallback to file path if available
                if (!empty($row['QRCode']) && file_exists($row['QRCode'])) {
                    $adminId = $_SESSION['AdminID'] ?? null;
                    logAudit($pdo, $adminId, 'QR_DOWNLOAD', 'Holding', $accNo, ['source' => 'file']);
                    header_remove();
                    header('Content-Type: image/png');
                    header('Content-Disposition: attachment; filename="qr_' . basename($accNo) . '.png"');
                    readfile($row['QRCode']);
                    exit;
                }
            }

            sendJson(['success' => false, 'message' => 'QR not found'], 404);
            break;
            
        case 'update':
            // Update book details
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            // Accept JSON body or form-encoded/FormData POSTs
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (empty($data) && !empty($_POST)) {
                // If the client sent FormData (multipart/form-data) or application/x-www-form-urlencoded
                $data = $_POST;
            }
            if (empty($data) && !empty($_REQUEST)) {
                $data = $_REQUEST;
            }

            $catNo = $data['CatNo'] ?? 0;

            if (!$catNo) {
                sendJson(['success' => false, 'message' => 'Catalog number is required'], 400);
            }
            
            $stmt = $pdo->prepare("
                UPDATE Books 
                SET Title = ?, SubTitle = ?, Author1 = ?, Author2 = ?, Author3 = ?, 
                    Publisher = ?, Place = ?, Year = ?, Edition = ?, Vol = ?, Pages = ?, 
                    ISBN = ?, Subject = ?, Language = ?
                WHERE CatNo = ?
            ");
            
            $stmt->execute([
                $data['Title'] ?? null,
                $data['SubTitle'] ?? null,
                $data['Author1'] ?? null,
                $data['Author2'] ?? null,
                $data['Author3'] ?? null,
                $data['Publisher'] ?? null,
                $data['Place'] ?? null,
                $data['Year'] ?? null,
                $data['Edition'] ?? null,
                $data['Vol'] ?? null,
                $data['Pages'] ?? null,
                $data['ISBN'] ?? null,
                $data['Subject'] ?? null,
                $data['Language'] ?? 'English',
                $catNo
            ]);
            $adminId = $_SESSION['AdminID'] ?? null;
            logAudit($pdo, $adminId, 'BOOK_UPDATE', 'Books', $catNo, [
                'updatedFields' => array_keys($data ?? [])
            ]);
            
            sendJson(['success' => true, 'message' => 'Book updated successfully']);
            break;
            
        case 'add-holding':
            // Add a new holding/copy for existing book
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO Holding (AccNo, CatNo, AccDate, ClassNo, BookNo, 
                                    Status, Location, Section, Collection)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['AccNo'],
                $data['CatNo'],
                $data['AccDate'] ?? date('Y-m-d'),
                $data['ClassNo'] ?? null,
                $data['BookNo'] ?? null,
                'Available',
                $data['Location'] ?? null,
                $data['Section'] ?? null,
                $data['Collection'] ?? null
            ]);

            $adminId = $_SESSION['AdminID'] ?? null;
            logAudit($pdo, $adminId, 'HOLDING_ADD', 'Holding', $data['AccNo'] ?? null, [
                'CatNo' => $data['CatNo'] ?? null
            ]);
            
            sendJson(['success' => true, 'message' => 'Holding added successfully']);
            break;
            
        case 'holdings':
            // Return all holdings with book title for Holdings tab
            $sql = "SELECT h.*, b.Title FROM Holding h LEFT JOIN Books b ON h.CatNo = b.CatNo ORDER BY h.AccNo DESC LIMIT 100";
            $stmt = $pdo->query($sql);
            $holdings = $stmt->fetchAll();
            sendJson(['success' => true, 'data' => $holdings]);
            break;

        case 'search':
            // Quick search books
            $query = $_GET['q'] ?? '';
            
            if (strlen($query) < 2) {
                sendJson(['success' => true, 'data' => []]);
            }
            
            $books = searchBooks($pdo, $query, 20);
            sendJson(['success' => true, 'data' => $books]);
            break;
            
        case 'subjects':
            // Get list of all subjects
            $stmt = $pdo->query("
                SELECT DISTINCT Subject 
                FROM Books 
                WHERE Subject IS NOT NULL 
                ORDER BY Subject
            ");
            $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            sendJson(['success' => true, 'data' => $subjects]);
            break;
            
        case 'holding-status':
            // Update holding status
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $accNo = $data['accNo'] ?? '';
            $status = $data['status'] ?? '';
            
            $validStatuses = ['Available', 'Issued', 'Damaged', 'Lost', 'Repair', 'Reserved'];
            
            if (!in_array($status, $validStatuses)) {
                sendJson(['success' => false, 'message' => 'Invalid status'], 400);
            }
            
            $stmt = $pdo->prepare("UPDATE Holding SET Status = ? WHERE AccNo = ?");
            $stmt->execute([$status, $accNo]);

            $adminId = $_SESSION['AdminID'] ?? null;
            logAudit($pdo, $adminId, 'HOLDING_STATUS_UPDATE', 'Holding', $accNo, [
                'status' => $status
            ]);
            
            sendJson(['success' => true, 'message' => 'Status updated successfully']);
            break;

        case 'update-holding':
            // Update holding details including AccNo, status, location, section
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $originalAccNo = $data['originalAccNo'] ?? '';
            $newAccNo = $data['newAccNo'] ?? '';
            $status = $data['status'] ?? '';
            $location = $data['location'] ?? '';
            $section = $data['section'] ?? '';
            
            if (!$originalAccNo || !$newAccNo) {
                sendJson(['success' => false, 'message' => 'AccNo is required'], 400);
            }

            $validStatuses = ['Available', 'Issued', 'Damaged', 'Lost', 'Repair', 'Reserved'];
            if ($status && !in_array($status, $validStatuses)) {
                sendJson(['success' => false, 'message' => 'Invalid status'], 400);
            }

            try {
                $pdo->beginTransaction();

                // Check if new AccNo already exists (if changed)
                if ($newAccNo !== $originalAccNo) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Holding WHERE AccNo = ?");
                    $stmt->execute([$newAccNo]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception('AccNo already exists. Please use a unique value.');
                    }
                }

                // Update holding
                $stmt = $pdo->prepare("UPDATE Holding SET AccNo = ?, Status = ?, Location = ?, Section = ? WHERE AccNo = ?");
                $stmt->execute([$newAccNo, $status, $location, $section, $originalAccNo]);

                $pdo->commit();

                $adminId = $_SESSION['AdminID'] ?? null;
                logAudit($pdo, $adminId, 'HOLDING_UPDATE', 'Holding', $newAccNo, [
                    'originalAccNo' => $originalAccNo,
                    'newAccNo' => $newAccNo,
                    'status' => $status,
                    'location' => $location,
                    'section' => $section
                ]);

                sendJson(['success' => true, 'message' => 'Holding updated successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                sendJson(['success' => false, 'message' => $e->getMessage()], 400);
            }
            break;
            
        case 'delete':
            // Delete book (only if no holdings exist)
            if ($method !== 'POST') {
                sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $catNo = $data['CatNo'] ?? 0;
            
            // Check if any holdings exist
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Holding WHERE CatNo = ?");
            $stmt->execute([$catNo]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                sendJson(['success' => false, 'message' => 'Cannot delete book with existing holdings'], 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM Books WHERE CatNo = ?");
            $stmt->execute([$catNo]);

            $adminId = $_SESSION['AdminID'] ?? null;
            logAudit($pdo, $adminId, 'BOOK_DELETE', 'Books', $catNo, null);
            
            sendJson(['success' => true, 'message' => 'Book deleted successfully']);
            break;
            
        case 'stats':
            // Get database-wide statistics
            // Total unique books
            $totalBooksStmt = $pdo->query("SELECT COUNT(DISTINCT CatNo) as total FROM Books");
            $totalBooks = (int)$totalBooksStmt->fetchColumn();
            
            // Total copies in Holding table
            $totalCopiesStmt = $pdo->query("SELECT COUNT(*) as total FROM Holding");
            $totalCopies = (int)$totalCopiesStmt->fetchColumn();
            
            // Available copies
            $availableCopiesStmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE Status = 'Available'");
            $availableCopies = (int)$availableCopiesStmt->fetchColumn();
            
            // Issued copies
            $issuedCopiesStmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE Status = 'Issued'");
            $issuedCopies = (int)$issuedCopiesStmt->fetchColumn();
            
            sendJson([
                'success' => true,
                'stats' => [
                    'totalBooks' => $totalBooks,
                    'totalCopies' => $totalCopies,
                    'availableCopies' => $availableCopies,
                    'issuedCopies' => $issuedCopies
                ]
            ]);
            break;
            
        default:
            sendJson(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendJson(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
