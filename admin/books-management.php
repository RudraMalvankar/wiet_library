<?php
session_start();

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// ============================================================
// DATA SOURCE: DATABASE (Fully Integrated)
// ============================================================
// ✅ List books - FETCHES from api/books.php?action=list
// ✅ Search books - Uses api/books.php?action=search
// ✅ Add books - POSTS to api/books.php?action=add
// ✅ Update books - POSTS to api/books.php?action=update
// ✅ Add holdings - POSTS to api/books.php?action=add-holding
// ============================================================

// --- Add Book Backend Logic (from books_add.php) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['author1'], $_POST['publisher'])) {
    $bookData = [
        'Title' => $_POST['title'],
        'Author1' => $_POST['author1'],
        'Author2' => $_POST['author2'] ?? '',
        'Author3' => $_POST['author3'] ?? '',
        'Publisher' => $_POST['publisher'],
        'Year' => $_POST['year'],
        'ISBN' => $_POST['isbn'],
        'Edition' => $_POST['edition'],
        'Language' => $_POST['language'],
        'Subject' => $_POST['subject'],
        'Keywords' => $_POST['keywords'],
        'Pages' => $_POST['pages'],
        'Price' => $_POST['price'],
        'CatNo' => null
    ];
    $stmt = $pdo->prepare("INSERT INTO Books (Title, Author1, Author2, Author3, Publisher, Year, ISBN, Edition, Language, Subject, Keywords, Pages, Price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $bookData['Title'], $bookData['Author1'], $bookData['Author2'], $bookData['Author3'], $bookData['Publisher'], $bookData['Year'], $bookData['ISBN'], $bookData['Edition'], $bookData['Language'], $bookData['Subject'], $bookData['Keywords'], $bookData['Pages'], $bookData['Price']
    ]);
    $bookData['CatNo'] = $pdo->lastInsertId();
    $acqData = [
        'CatNo' => $bookData['CatNo'],
        'Supplier' => $_POST['supplier'],
        'InvoiceNo' => $_POST['invoice_no'],
        'InvoiceDate' => $_POST['invoice_date'],
        'OrderNo' => $_POST['order_no'],
        'OrderDate' => $_POST['order_date'],
        'ReceivedDate' => $_POST['received_date'],
        'Quantity' => $_POST['quantity'],
        'TotalCost' => $_POST['total_cost']
    ];
    $stmt = $pdo->prepare("INSERT INTO Acquisition (CatNo, Supplier, InvoiceNo, InvoiceDate, OrderNo, OrderDate, ReceivedDate, Quantity, TotalCost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $acqData['CatNo'], $acqData['Supplier'], $acqData['InvoiceNo'], $acqData['InvoiceDate'], $acqData['OrderNo'], $acqData['OrderDate'], $acqData['ReceivedDate'], $acqData['Quantity'], $acqData['TotalCost']
    ]);
    $numCopies = (int)$_POST['num_copies'];
    for ($i = 1; $i <= $numCopies; $i++) {
        $accNo = function_exists('generateAccNo') ? generateAccNo($bookData['CatNo'], $i) : ($bookData['CatNo'] . '-' . str_pad($i, 3, '0', STR_PAD_LEFT));
        $barcodePath = function_exists('generateBarcode') ? generateBarcode($accNo) : '';
        $qrPath = function_exists('generateQR') ? generateQR($accNo) : '';
        $stmt = $pdo->prepare("INSERT INTO Holding (AccNo, CatNo, CopyNo, BookNo, AccDate, ClassNo, Status, Location, Section, Collection, BarCode, Binding, `Condition`, Remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $accNo,
            $bookData['CatNo'],
            $i,
            $_POST['book_no'],
            $_POST['acc_date'],
            $_POST['class_no'],
            'Available',
            $_POST['location'],
            $_POST['section'],
            $_POST['collection'],
            $barcodePath,
            $_POST['binding'],
            'Good',
            $_POST['remarks'] ?? ''
        ]);
    }
    echo "<div class='alert alert-success'>Book(s) added successfully!</div>";
}
// --- Helper Generators (from books_add.php) ---
if (!function_exists('generateAccNo')) {
    function generateAccNo($catNo, $copyIndex) {
        $catPart = preg_replace('/[^A-Za-z0-9]/', '', (string)$catNo);
        $copyPart = str_pad((string)$copyIndex, 3, '0', STR_PAD_LEFT);
        return $catPart . '-' . $copyPart;
    }
}
if (!function_exists('generateBarcode')) {
    function generateBarcode($text) {
        $baseDir = realpath(__DIR__ . '/../storage');
        if (!$baseDir) { $baseDir = __DIR__ . '/../storage'; }
        $dir = $baseDir . '/barcodes';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $filename = $dir . '/barcode_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$text) . '.png';
        if (function_exists('imagecreatetruecolor')) {
            $width = 480; $height = 140;
            $img = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefilledrectangle($img, 0, 0, $width, $height, $white);
            $hash = md5((string)$text);
            $x = 10; $maxX = $width - 10;
            for ($i = 0; $i < strlen($hash) && $x < $maxX; $i++) {
                $val = hexdec($hash[$i]);
                $barWidth = max(1, ($val % 4) + 1);
                $barHeight = 100 + ($val % 20);
                imagefilledrectangle($img, $x, 20, $x + $barWidth, 20 + $barHeight, $black);
                $x += $barWidth + 2;
            }
            if (function_exists('imagestring')) {
                imagestring($img, 4, 10, $height - 20, (string)$text, $black);
            }
            imagepng($img, $filename);
            imagedestroy($img);
        } else {
            file_put_contents($filename . '.txt', (string)$text);
            $filename .= '.txt';
        }
        return $filename;
    }
}
if (!function_exists('generateQR')) {
    function generateQR($text) {
        $baseDir = realpath(__DIR__ . '/../storage');
        if (!$baseDir) { $baseDir = __DIR__ . '/../storage'; }
        $dir = $baseDir . '/qrcodes';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $filename = $dir . '/qr_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$text) . '.png';
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
        } else {
            file_put_contents($filename . '.txt', (string)$text);
            $filename .= '.txt';
        }
        return $filename;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .books-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .books-title {
            color: #263c79;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 15px 0;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        /* Desktop view */
        @media (min-width: 768px) {
            .books-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .books-title {
                margin: 0;
            }
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .btn-primary {
            background-color: #263c79;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1e2d5f;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .tabs-container {
            margin-bottom: 20px;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 20px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: #263c79;
            border-bottom-color: #cfac69;
            font-weight: 600;
        }

        .tab-btn:hover {
            color: #263c79;
            background-color: rgba(207, 172, 105, 0.1);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .search-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .search-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .form-group label {
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #cfac69;
            box-shadow: 0 0 0 2px rgba(207, 172, 105, 0.2);
        }

        .books-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .books-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .books-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .books-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .action-links {
            display: flex;
            gap: 8px;
        }

        .action-links a {
            padding: 4px 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-view {
            background-color: #17a2b8;
            color: white;
        }

        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #cfac69;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #263c79;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            color: #263c79;
            text-decoration: none;
            border-radius: 4px;
        }

        .page-link:hover,
        .page-link.active {
            background-color: #263c79;
            color: white;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        /* Modal Styles (improved: centered, fits viewport, accessible) */
        .modal {
            display: none; /* shown by JS by setting display:block */
            position: fixed;
            z-index: 110000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
            overflow: auto;
        }

        .modal-content {
            background-color: white;
            padding: 0;
            border-radius: 8px;
            width: 100%;
            max-width: 900px;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
            position: relative;
            z-index: 110001;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }

        .modal-header {
            background-color: #263c79;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .close {
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 20px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .form-group-modal {
            flex: 1;
            min-width: 200px;
        }

        .form-group-modal label {
            display: block;
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-group-modal input,
        .form-group-modal select,
        .form-group-modal textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group-modal textarea {
            min-height: 80px;
            resize: vertical;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Holdings specific styles */
        .holdings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .holding-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            background: white;
        }

        .holding-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .accession-no {
            font-weight: 600;
            color: #263c79;
            font-size: 16px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-available {
            background-color: #d4edda;
            color: #155724;
        }

        .status-issued {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-reserved {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-damaged {
            background-color: #f8d7da;
            color: #721c24;
        }

        .holding-details {
            font-size: 14px;
            color: #6c757d;
        }

        .holding-detail {
            margin-bottom: 5px;
        }

        /* Acquisition specific styles */
        .acquisition-timeline {
            position: relative;
            margin-top: 20px;
        }

        .timeline-item {
            display: flex;
            margin-bottom: 20px;
            position: relative;
        }

        .timeline-marker {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #cfac69;
            margin-right: 15px;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .timeline-content {
            flex: 1;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid #cfac69;
        }

        .timeline-date {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
        }

        .timeline-title {
            font-weight: 600;
            color: #263c79;
            margin: 5px 0;
        }

        .timeline-description {
            font-size: 14px;
            color: #495057;
        }

        /* Reports specific styles */
        .report-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .report-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-color: #cfac69;
        }

        .report-icon {
            font-size: 24px;
            color: #263c79;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 18px;
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
        }

        .report-description {
            color: #6c757d;
            font-size: 14px;
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        @media (max-width: 768px) {
            .search-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            .books-table {
                font-size: 12px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
            }

            .modal-content {
                margin-top: 150px;
                /* Adjust for mobile navbar */
                width: 98%;
                max-height: 80vh;
            }
        }

        @media (max-width: 480px) {
            .modal-content {
                margin-top: 135px;
                /* Adjust for smaller mobile navbar */
                width: 99%;
                max-height: 75vh;
            }
        }

        /* Inline Form Styles */
        .form-section {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* QR Lightbox */
        .qr-lightbox {
            display: none;
            position: fixed;
            z-index: 120000;
            left: 0; top: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.8);
            align-items: center;
            justify-content: center;
        }
        .qr-lightbox.open { display:flex; }
        .qr-lightbox-content {
            max-width: 90vw;
            max-height: 90vh;
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.5);
            display:flex; align-items:center; justify-content:center;
        }
        .qr-lightbox-content img { max-width: 100%; max-height: 80vh; }
        .qr-lightbox-close {
            position: absolute; top: 14px; right: 20px; color: white; font-size: 24px; cursor: pointer;
        }

        /* Small spinner for inline status */
        .inline-spinner { display:inline-block; width:18px; height:18px; border:3px solid rgba(0,0,0,0.1); border-top-color:#263c79; border-radius:50%; animation:spin 0.8s linear infinite; vertical-align:middle; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #263c79;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #cfac69;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .form-group-modal {
            flex: 1;
            min-width: 200px;
        }

        .form-group-modal label {
            display: block;
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-group-modal input,
        .form-group-modal select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group-modal input:focus,
        .form-group-modal select:focus {
            outline: none;
            border-color: #cfac69;
            box-shadow: 0 0 0 2px rgba(207, 172, 105, 0.2);
        }

        .required {
            color: #dc3545;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px !important;
            font-size: 14px !important;
        }
    </style>
</head>

<body>
    <div class="books-header">
        <div class="books-title">
            <i class="fas fa-book"></i> Books Management
        </div>
        <div class="action-buttons" style="gap: 8px;">
            <button class="btn btn-success" onclick="openAddBookModal()">
                <i class="fas fa-plus"></i> Add Book
            </button>
            <button class="btn btn-primary" onclick="openBulkImportModal()">
                <i class="fas fa-upload"></i> Bulk Import
            </button>
            <a href="?export_books_csv=1" class="btn btn-success" style="min-width:120px;">Export CSV</a>
        </div>
    </div>

    <?php if (!empty($importMsg)) echo $importMsg; ?>
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalBooks">-</div>
            <div class="stat-label">Total Books</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalCopies">-</div>
            <div class="stat-label">Total Copies</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="availableCopies">-</div>
            <div class="stat-label">Available Copies</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="issuedCopies">-</div>
            <div class="stat-label">Issued Copies</div>
        </div>
    </div>

    <!-- Inline Add Book Form (Advanced) -->
    

    <!-- Remove duplicate tabs-container and content blocks -->

    <!-- duplicate Add Book modal removed (inline form is used) -->
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookVarTitle">Variant Title</label>
                            <input type="text" id="bookVarTitle" name="VarTitle">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookFormat">Format</label>
                            <select id="bookFormat" name="Format">
                                <option value="Book">Book</option>
                                <option value="Journal">Journal</option>
                                <option value="Magazine">Magazine</option>
                                <option value="Thesis">Thesis</option>
                                <option value="Report">Report</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookAuthor1">Primary Author *</label>
                            <input type="text" id="bookAuthor1" name="Author1" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="bookAuthor2">Secondary Author</label>
                            <input type="text" id="bookAuthor2" name="Author2">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookAuthor3">Third Author</label>
                            <input type="text" id="bookAuthor3" name="Author3">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookCorpAuthor">Corporate Author</label>
                            <input type="text" id="bookCorpAuthor" name="CorpAuthor">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookEditors">Editors</label>
                            <input type="text" id="bookEditors" name="Editors">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookISBN">ISBN</label>
                            <input type="text" id="bookISBN" name="ISBN" placeholder="978-0-123456-78-9">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookLanguage">Language</label>
                            <select id="bookLanguage" name="Language">
                                <option value="English">English</option>
                                <option value="Hindi">Hindi</option>
                                <option value="Marathi">Marathi</option>
                                <option value="Sanskrit">Sanskrit</option>
                                <option value="French">French</option>
                                <option value="German">German</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookPublisher">Publisher</label>
                            <input type="text" id="bookPublisher" name="Publisher">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookPlace">Place of Publication</label>
                            <input type="text" id="bookPlace" name="Place">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookYear">Publication Year</label>
                            <input type="number" id="bookYear" name="Year" min="1900" max="2030">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookSubject">Subject</label>
                            <input type="text" id="bookSubject" name="Subject">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookEdition">Edition</label>
                            <input type="text" id="bookEdition" name="Edition">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookVol">Volume</label>
                            <input type="text" id="bookVol" name="Vol">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookPages">Pages</label>
                            <input type="number" id="bookPages" name="Pages" min="1">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="numCopies">Number of Copies</label>
                            <input type="number" id="numCopies" name="numCopies" min="1" value="1" onchange="renderHoldingsSection()">
                        </div>
                    </div>

                    <div id="holdingsSection"></div>

                    <div class="form-row">
                        <button type="button" class="btn btn-link" onclick="toggleAcquisitionSection()">Acquisition Details</button>
                    </div>
                    <div id="acquisitionSection" style="display:none; border:1px solid #eee; padding:15px; margin-bottom:10px; border-radius:6px; background:#faf9f6;">
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="acqSupplier">Supplier</label>
                                <input type="text" id="acqSupplier" name="Supplier">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqInvoiceNo">Invoice No</label>
                                <input type="text" id="acqInvoiceNo" name="InvoiceNo">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqInvoiceDate">Invoice Date</label>
                                <input type="date" id="acqInvoiceDate" name="InvoiceDate">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="acqOrderNo">Order No</label>
                                <input type="text" id="acqOrderNo" name="OrderNo">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqOrderDate">Order Date</label>
                                <input type="date" id="acqOrderDate" name="OrderDate">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqReceivedDate">Received Date</label>
                                <input type="date" id="acqReceivedDate" name="ReceivedDate">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="acqQuantity">Quantity</label>
                                <input type="number" id="acqQuantity" name="Quantity" min="1">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqTotalCost">Total Cost</label>
                                <input type="number" id="acqTotalCost" name="TotalCost" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="form-actions" style="justify-content:flex-start;">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i>
                            Add New Book
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('catalog')">
                <i class="fas fa-list"></i>
                Books Catalog
            </button>
            <button class="tab-btn" onclick="showTab('holdings')">
                <i class="fas fa-warehouse"></i>
                Holdings Management
            </button>
            <button class="tab-btn" onclick="showTab('acquisition')">
                <i class="fas fa-shopping-cart"></i>
                Acquisition
            </button>
            <button class="tab-btn" onclick="showTab('reports')">
                <i class="fas fa-chart-pie"></i>
                Reports
            </button>
        </div>

        <!-- Books Catalog Tab -->
        <div id="catalog" class="tab-content active">
            <div class="search-filters">
                <div class="search-row">
                    <div class="form-group">
                        <label for="searchTitle">Title</label>
                        <input type="text" id="searchTitle" class="form-control" placeholder="Search by title...">
                    </div>
                    <div class="form-group">
                        <label for="searchAuthor">Author</label>
                        <input type="text" id="searchAuthor" class="form-control" placeholder="Search by author...">
                    </div>
                    <div class="form-group">
                        <label for="searchISBN">ISBN</label>
                        <input type="text" id="searchISBN" class="form-control" placeholder="Search by ISBN...">
                    </div>
                    <div class="form-group">
                        <label for="searchSubject">Subject</label>
                        <input type="text" id="searchSubject" class="form-control" placeholder="Search by subject...">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="searchBooks()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div id="booksTableContainer">
                <!-- Books table will be loaded here -->
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Loading books...</p>
                </div>
            </div>
        </div>

        <!-- Holdings Management Tab -->
        <div id="holdings" class="tab-content">
            <div id="holdingsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-warehouse" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Holdings Management</h3>
                    <p>Manage individual book copies, track locations, and monitor availability.</p>
                </div>
            </div>
        </div>

        <!-- Acquisition Tab -->
        <div id="acquisition" class="tab-content">
            <div id="acquisitionContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-shopping-cart" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Acquisition Management</h3>
                    <p>Track book purchases, orders, and acquisition processes.</p>
                </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <div id="reportsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-chart-pie" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Books Reports</h3>
                    <p>Generate reports on books, holdings, and acquisition statistics.</p>
                </div>
            </div>
        </div>
    </div>

                <!-- Add Book Modal -->
    <div id="addBookModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Book</h3>
                <button class="close" onclick="closeModal('addBookModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addBookForm">
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookTitle">Title *</label>
                            <input type="text" id="bookTitle" name="Title" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="bookSubTitle">Sub Title</label>
                            <input type="text" id="bookSubTitle" name="SubTitle">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookVarTitle">Variant Title</label>
                            <input type="text" id="bookVarTitle" name="VarTitle">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookFormat">Format</label>
                            <select id="bookFormat" name="Format">
                                <option value="Book">Book</option>
                                <option value="Journal">Journal</option>
                                <option value="Magazine">Magazine</option>
                                <option value="Thesis">Thesis</option>
                                <option value="Report">Report</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookAuthor1">Primary Author *</label>
                            <input type="text" id="bookAuthor1" name="Author1" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="bookAuthor2">Secondary Author</label>
                            <input type="text" id="bookAuthor2" name="Author2">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookAuthor3">Third Author</label>
                            <input type="text" id="bookAuthor3" name="Author3">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookCorpAuthor">Corporate Author</label>
                            <input type="text" id="bookCorpAuthor" name="CorpAuthor">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookEditors">Editors</label>
                            <input type="text" id="bookEditors" name="Editors">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookISBN">ISBN</label>
                            <input type="text" id="bookISBN" name="ISBN" placeholder="978-0-123456-78-9">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookLanguage">Language</label>
                            <select id="bookLanguage" name="Language">
                                <option value="English">English</option>
                                <option value="Hindi">Hindi</option>
                                <option value="Marathi">Marathi</option>
                                <option value="Sanskrit">Sanskrit</option>
                                <option value="French">French</option>
                                <option value="German">German</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookPublisher">Publisher</label>
                            <input type="text" id="bookPublisher" name="Publisher">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookPlace">Place of Publication</label>
                            <input type="text" id="bookPlace" name="Place">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookYear">Publication Year</label>
                            <input type="number" id="bookYear" name="Year" min="1900" max="2030">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookSubject">Subject</label>
                            <input type="text" id="bookSubject" name="Subject">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookEdition">Edition</label>
                            <input type="text" id="bookEdition" name="Edition">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookVol">Volume</label>
                            <input type="text" id="bookVol" name="Vol">
                        </div>
                        <div class="form-group-modal">
                            <label for="bookPages">Pages</label>
                            <input type="number" id="bookPages" name="Pages" min="1">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="numCopies">Number of Copies</label>
                            <input type="number" id="numCopies" name="numCopies" min="1" value="1" onchange="renderHoldingsSection()">
                        </div>
                    </div>

                    <div id="holdingsSection"></div>

                    <div class="form-row">
                        <button type="button" class="btn btn-link" onclick="toggleAcquisitionSection()">Acquisition Details</button>
                    </div>
                    <div id="acquisitionSection" style="display:none; border:1px solid #eee; padding:15px; margin-bottom:10px; border-radius:6px; background:#faf9f6;">
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="acqSupplier">Supplier</label>
                                <input type="text" id="acqSupplier" name="Supplier">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqInvoiceNo">Invoice No</label>
                                <input type="text" id="acqInvoiceNo" name="InvoiceNo">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqInvoiceDate">Invoice Date</label>
                                <input type="date" id="acqInvoiceDate" name="InvoiceDate">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="acqOrderNo">Order No</label>
                                <input type="text" id="acqOrderNo" name="OrderNo">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqOrderDate">Order Date</label>
                                <input type="date" id="acqOrderDate" name="OrderDate">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqReceivedDate">Received Date</label>
                                <input type="date" id="acqReceivedDate" name="ReceivedDate">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="acqQuantity">Quantity</label>
                                <input type="number" id="acqQuantity" name="Quantity" min="1">
                            </div>
                            <div class="form-group-modal">
                                <label for="acqTotalCost">Total Cost</label>
                                <input type="number" id="acqTotalCost" name="TotalCost" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addBookModal')">Cancel</button>
                        <button type="button" class="btn btn-success" onclick="saveBook()">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div> 

    <!-- Book Details/Edit Modal -->
            <div id="bookDetailsModal" class="modal" style="display:none; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:1000; background:rgba(0,0,0,0.5);">
                <div class="modal-content book-modal-scrollable" style="max-height:90vh; overflow-y:auto; display:flex; flex-direction:column; margin:auto;">
                <div class="modal-header">
                    <h3 class="modal-title" id="bookDetailsModalTitle">Book Details</h3>
                    <button class="close" onclick="closeModal('bookDetailsModal')">&times;</button>
                </div>
                <div class="modal-body" id="bookDetailsModalBody">
                    <!-- Book details will be loaded here -->
                    <div style="text-align:center; color:#6c757d; padding:30px;">
                        <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
                        <p>Loading book details...</p>
                    </div>
                </div>
                <div class="modal-footer" id="bookDetailsModalFooter" style="display:none;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('bookDetailsModal')">Close</button>
                    <button type="button" class="btn btn-success" id="saveBookEditBtn" onclick="saveBookEdit()" style="display:none;">Save Changes</button>
                </div>
            </div>
        </div>
        <!-- /* Make book details modal scrollable and fit viewport */ -->
       <style> .book-modal-scrollable {
            max-height: 90vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            margin: auto;
        }
        .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
        }
        .modal-footer {
            flex: 0 0 auto;
            background: #fff;
        }
</style>
    <!-- Bulk Import Modal (info only, real import is via action-buttons form) -->
    <div id="bulkImportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Bulk Import Books</h3>
                <button class="close" onclick="closeModal('bulkImportModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-file-excel" style="font-size: 48px; color: #28a745; margin-bottom: 15px;"></i>
                    <h4 style="color: #263c79; margin-bottom: 15px;">Import Books from CSV</h4>
                    <p style="margin-bottom: 20px; color: #6c757d;">Use the Import CSV button in the action bar above to upload a CSV file containing book information.</p>
                    <div style="text-align: left; margin-top: 20px;">
                        <h5 style="color: #263c79;">Required Columns:</h5>
                        <ul style="color: #6c757d; font-size: 14px;">
                            <li>CatNo</li>
                            <li>Title (required)</li>
                            <li>Author1 (required)</li>
                            <li>Author2</li>
                            <li>Author3</li>
                            <li>Publisher</li>
                            <li>Year</li>
                            <li>ISBN</li>
                            <li>Edition</li>
                            <li>Language</li>
                            <li>Subject</li>
                            <li>Keywords</li>
                            <li>Pages</li>
                            <li>Price</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('bulkImportModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- QR Lightbox -->
    <div id="qrLightbox" class="qr-lightbox" onclick="closeQrLightbox(event)">
        <div class="qr-lightbox-close" onclick="closeQrLightbox(event)">&times;</div>
        <div class="qr-lightbox-content">
            <img id="qrLightboxImg" src="" alt="QR code" />
            <div style="margin-top:10px; text-align:center;">
                <a id="qrLightboxDownload" class="btn btn-sm btn-success" href="#" download>Download</a>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked button
            if (event && event.target) {
                event.target.classList.add('active');
            }

            // Load content based on tab
            loadTabContent(tabName);
        }

        // Ensure books table loads on initial page load
        // Only load books table once on initial page load
        let booksTableLoaded = false;
        document.addEventListener('DOMContentLoaded', function() {
            // Load database-wide statistics
            loadStatistics();
            
            if (!booksTableLoaded) {
                booksTableLoaded = true;
                loadBooksTable();
            }
        });

        function loadTabContent(tabName) {
            switch (tabName) {
                case 'catalog':
                    loadBooksTable();
                    break;
                case 'holdings':
                    loadHoldingsContent();
                    break;
                case 'acquisition':
                    loadAcquisitionContent();
                    break;
                case 'reports':
                    loadReportsContent();
                    break;
            }
        }

        // Paginated Books Table
        let booksPage = 1;
        let booksPageSize = 20;
        let booksTotal = 0;

        // Function to load database-wide statistics
        async function loadStatistics() {
            try {
                const response = await fetch('api/books.php?action=stats');
                if (!response.ok) throw new Error('API error: ' + response.status);
                const result = await response.json();
                
                if (result.success && result.stats) {
                    const totalBooksElem = document.getElementById('totalBooks');
                    const totalCopiesElem = document.getElementById('totalCopies');
                    const availableCopiesElem = document.getElementById('availableCopies');
                    const issuedCopiesElem = document.getElementById('issuedCopies');
                    
                    if (totalBooksElem) totalBooksElem.textContent = result.stats.totalBooks;
                    if (totalCopiesElem) totalCopiesElem.textContent = result.stats.totalCopies;
                    if (availableCopiesElem) availableCopiesElem.textContent = result.stats.availableCopies;
                    if (issuedCopiesElem) issuedCopiesElem.textContent = result.stats.issuedCopies;
                }
            } catch (error) {
                console.error('Failed to load statistics:', error);
            }
        }

        async function loadBooksTable(page = 1) {
            booksPage = page;
            const booksTableContainer = document.getElementById('booksTableContainer');
            booksTableContainer.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #263c79;"></i><p>Loading books...</p></div>';
            try {
                const response = await fetch(`api/books.php?action=list&page=${booksPage}&pageSize=${booksPageSize}`);
                if (!response.ok) throw new Error('API error: ' + response.status);
                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON from books list API. Raw response:\n', text);
                    throw new Error('Invalid JSON response from API: ' + e.message);
                }
                booksTotal = result.total || 0;
                
                let tableHTML = `
                    <table class="books-table">
                        <thead>
                            <tr>
                                <th>Cat No.</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Year</th>
                                <th>Subject</th>
                                <th>Copies</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                if (result.success && result.data && result.data.length > 0) {
                    result.data.forEach(book => {
                        tableHTML += `
                            <tr>
                                <td>${book.CatNo}</td>
                                <td><strong>${book.Title}</strong></td>
                                <td>${book.Author1}</td>
                                <td>${book.ISBN || 'N/A'}</td>
                                <td>${book.Year || 'N/A'}</td>
                                <td>${book.Subject || 'General'}</td>
                                <td>${book.TotalCopies || 0}</td>
                                <td>${book.AvailableCopies || 0}</td>
                                <td class="action-links">
                                    <a href="#" class="btn-view" onclick="viewBook('${book.CatNo}')">View</a>
                                    <a href="#" class="btn-edit" onclick="editBook('${book.CatNo}')">Edit</a>
                                    <a href="#" class="btn-delete" onclick="deleteBook('${book.CatNo}')">Delete</a>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tableHTML += '<tr><td colspan="9" style="text-align: center; padding: 20px; color: #6c757d;">No books found in the library</td></tr>';
                }
                tableHTML += `
                        </tbody>
                    </table>
                `;
                // Pagination controls
                const totalPages = Math.ceil(booksTotal / booksPageSize);
                if (totalPages > 1) {
                    tableHTML += '<div class="pagination">';
                    if (booksPage > 1) {
                        tableHTML += `<a href="#" class="page-link" onclick="loadBooksTable(${booksPage - 1});return false;">Previous</a>`;
                    }
                    for (let i = 1; i <= totalPages; i++) {
                        tableHTML += `<a href="#" class="page-link${i === booksPage ? ' active' : ''}" onclick="loadBooksTable(${i});return false;">${i}</a>`;
                    }
                    if (booksPage < totalPages) {
                        tableHTML += `<a href="#" class="page-link" onclick="loadBooksTable(${booksPage + 1});return false;">Next</a>`;
                    }
                    tableHTML += '</div>';
                }
                booksTableContainer.innerHTML = tableHTML;
            } catch (error) {
                console.error('Error loading books:', error);
                booksTableContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i><p>Error loading books. Please try again.</p></div>';
                // Reset stats on error
                const totalBooksElem = document.getElementById('totalBooks');
                if (totalBooksElem) totalBooksElem.textContent = '-';
                const totalCopiesElem = document.getElementById('totalCopies');
                const availableCopiesElem = document.getElementById('availableCopies');
                const issuedCopiesElem = document.getElementById('issuedCopies');
                if (totalCopiesElem) totalCopiesElem.textContent = '-';
                if (availableCopiesElem) availableCopiesElem.textContent = '-';
                if (issuedCopiesElem) issuedCopiesElem.textContent = '-';
            }
        }

        async function loadHoldingsContent() {
            // Show loading spinner
            document.getElementById('holdingsContent').innerHTML = `<div style="text-align: center; padding: 40px; color: #6c757d;"><i class='fas fa-spinner fa-spin' style='font-size: 24px;'></i><p>Loading holdings...</p></div>`;

            try {
                const response = await fetch('api/books.php?action=holdings');
                if (!response.ok) throw new Error('API error: ' + response.status);
                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON from holdings API. Raw response:\n', text);
                    throw new Error('Invalid JSON response from holdings API: ' + e.message);
                }
                if (!result.success || !result.data || result.data.length === 0) {
                    document.getElementById('holdingsContent').innerHTML = `<div style='text-align:center; color:#6c757d; padding:40px;'>No holdings found.</div>`;
                    return;
                }
                let html = `<div class='holdings-grid'>`;
                result.data.forEach(h => {
                    html += `<div class='holding-card'>
                        <div class='holding-header'>
                            <div class='accession-no'>${h.AccNo}</div>
                            <div class='status-badge status-${(h.Status||'available').toLowerCase()}'>${h.Status}</div>
                        </div>
                        <div class='holding-details'>
                            <div class='holding-detail'><strong>Book:</strong> ${h.Title || ''}</div>
                            <div class='holding-detail'><strong>Copy No:</strong> ${h.CopyNo || ''}</div>
                            <div class='holding-detail'><strong>Location:</strong> ${h.Location || ''}</div>
                            <div class='holding-detail'><strong>Barcode:</strong> ${h.BarCode || ''}</div>
                        </div>
                    </div>`;
                });
                html += `</div>`;
                document.getElementById('holdingsContent').innerHTML = html;
            } catch (err) {
                document.getElementById('holdingsContent').innerHTML = `<div style='color:#dc3545; text-align:center; padding:40px;'><i class='fas fa-exclamation-triangle'></i> Error loading holdings.</div>`;
            }
        }

        function loadAcquisitionContent() {
            const acquisitionHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin: 0;">Recent Acquisitions</h3>
                    <button class="btn btn-success" onclick="openAddAcquisitionModal()">
                        <i class="fas fa-plus"></i>
                        New Acquisition
                    </button>
                </div>

                <div class="acquisition-timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">March 15, 2024</div>
                            <div class="timeline-title">Order Placed - ORD001</div>
                            <div class="timeline-description">
                                Ordered 10 copies of "Introduction to Computer Science" from ABC Publishers
                                <br><strong>Amount:</strong> ₹5,000 | <strong>Status:</strong> Pending Delivery
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">March 10, 2024</div>
                            <div class="timeline-title">Books Received - ORD002</div>
                            <div class="timeline-description">
                                Received 5 copies of "Advanced Mathematics" from XYZ Publications
                                <br><strong>Amount:</strong> ₹3,500 | <strong>Status:</strong> Completed
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('acquisitionContent').innerHTML = acquisitionHTML;
        }

        function loadReportsContent() {
            const reportsHTML = `
                <div class="reports-grid">
                    <div class="report-card" onclick="generateReport('books-summary')">
                        <div class="report-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="report-title">Books Summary Report</div>
                        <div class="report-description">
                            Complete overview of all books in the library with statistics
                        </div>
                    </div>
                    
                    <div class="report-card" onclick="generateReport('holdings-status')">
                        <div class="report-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div class="report-title">Holdings Status Report</div>
                        <div class="report-description">
                            Status of all book copies including availability and locations
                        </div>
                    </div>
                    
                    <div class="report-card" onclick="generateReport('acquisition-history')">
                        <div class="report-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="report-title">Acquisition History</div>
                        <div class="report-description">
                            Complete history of book purchases and acquisitions
                        </div>
                    </div>
                    
                    <div class="report-card" onclick="generateReport('subject-wise')">
                        <div class="report-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="report-title">Subject-wise Distribution</div>
                        <div class="report-description">
                            Books categorized by subjects with statistics
                        </div>
                    </div>
                    
                    <div class="report-card" onclick="generateReport('publisher-wise')">
                        <div class="report-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="report-title">Publisher-wise Report</div>
                        <div class="report-description">
                            Books grouped by publishers with acquisition details
                        </div>
                    </div>
                    
                    <div class="report-card" onclick="generateReport('yearly-acquisitions')">
                        <div class="report-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="report-title">Yearly Acquisitions</div>
                        <div class="report-description">
                            Year-wise book acquisition trends and statistics
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('reportsContent').innerHTML = reportsHTML;
        }

        // Modal / UI functions
        // The Add Book form exists inline on the page; don't open a modal for adding.
        function openAddBookModal() {
            // Scroll to the inline Add Book form and focus the first input
            const inlineForm = document.getElementById('addBookForm');
            if (inlineForm) {
                inlineForm.scrollIntoView({behavior:'smooth', block:'center'});
                const firstInput = inlineForm.querySelector('input, select, textarea, button');
                if (firstInput) firstInput.focus();
            } else {
                // fallback to modal if inline not present
                const m = document.getElementById('addBookModal');
                if (m) m.style.display = 'flex';
            }
        }

        // Toggle acquisition details for the inline form
        function toggleAcquisitionSection() {
            const sec = document.getElementById('acquisitionSection');
            if (!sec) return;
            if (sec.style.display === 'none' || sec.style.display === '') {
                sec.style.display = 'block';
                // focus first field
                const f = sec.querySelector('input, select, textarea');
                if (f) f.focus();
            } else {
                sec.style.display = 'none';
            }
        }

        // Ensure any stray modals are hidden on load (prevents broken state where modals remain visible)
        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('.modal').forEach(m => {
                try { m.style.display = 'none'; } catch(e){}
            });
        });

        function openBulkImportModal() {
            // Redirect to bulk import section instead of opening modal
            window.parent.location.hash = 'bulk-import';

            // If we're in the main layout, trigger the sidebar navigation
            if (window.parent.document) {
                const bulkImportLink = window.parent.document.querySelector('[data-page="bulk-import"]');
                if (bulkImportLink) {
                    bulkImportLink.click();
                }
            } else {
                // Fallback - show a message
                alert('Redirecting to Bulk Import section...');
                console.log('Navigating to bulk import section');
            }
        }

        function closeModal(modalId) {
            // delegate to the global helper defined at the end of file
            if (typeof window.closeModal === 'function') {
                window.closeModal(modalId);
                return;
            }
            try { document.getElementById(modalId).style.display = 'none'; } catch(e){}
        }

        function saveBook() {
            // Collect form data
            const form = document.getElementById('addBookForm');
            // Build FormData with backend-friendly keys (lowercase underscore style)
            const fd = new FormData();
            // map main fields (form uses PascalCase names)
            fd.append('title', form.querySelector('[name="Title"]')?.value || '');
            fd.append('subtitle', form.querySelector('[name="SubTitle"]')?.value || '');
            fd.append('author1', form.querySelector('[name="Author1"]')?.value || '');
            fd.append('author2', form.querySelector('[name="Author2"]')?.value || '');
            fd.append('author3', form.querySelector('[name="Author3"]')?.value || '');
            fd.append('publisher', form.querySelector('[name="Publisher"]')?.value || '');
            fd.append('year', form.querySelector('[name="Year"]')?.value || '');
            fd.append('isbn', form.querySelector('[name="ISBN"]')?.value || '');
            fd.append('edition', form.querySelector('[name="Edition"]')?.value || '');
            fd.append('language', form.querySelector('[name="Language"]')?.value || '');
            fd.append('subject', form.querySelector('[name="Subject"]')?.value || '');
            fd.append('keywords', form.querySelector('[name="Keywords"]')?.value || '');
            fd.append('pages', form.querySelector('[name="Pages"]')?.value || '');
            fd.append('price', form.querySelector('[name="Price"]')?.value || '');
            // normalized copies field name expected by backend
            const numCopies = parseInt(form.querySelector('[name="numCopies"]')?.value || '1', 10) || 1;
            fd.append('num_copies', numCopies);

            // Map holdings - send the first holding fields (backend expects simple fields per copy)
            const firstHoldingIndex = 1;
            fd.append('book_no', document.getElementById('holdingBookNo_' + firstHoldingIndex)?.value || '');
            fd.append('acc_date', document.getElementById('holdingAccDate_' + firstHoldingIndex)?.value || '');
            fd.append('class_no', document.getElementById('holdingClassNo_' + firstHoldingIndex)?.value || '');
            fd.append('location', document.getElementById('holdingLocation_' + firstHoldingIndex)?.value || '');
            fd.append('section', document.getElementById('holdingSection_' + firstHoldingIndex)?.value || '');
            fd.append('collection', document.getElementById('holdingCollection_' + firstHoldingIndex)?.value || '');
            fd.append('binding', document.getElementById('holdingBinding_' + firstHoldingIndex)?.value || '');
            fd.append('remarks', document.getElementById('holdingRemarks_' + firstHoldingIndex)?.value || '');

            // Acquisition mapping (backend expects snake_case lowercase keys)
            fd.append('supplier', document.getElementById('acqSupplier')?.value || '');
            fd.append('invoice_no', document.getElementById('acqInvoiceNo')?.value || '');
            fd.append('invoice_date', document.getElementById('acqInvoiceDate')?.value || '');
            fd.append('order_no', document.getElementById('acqOrderNo')?.value || '');
            fd.append('order_date', document.getElementById('acqOrderDate')?.value || '');
            fd.append('received_date', document.getElementById('acqReceivedDate')?.value || '');
            fd.append('quantity', document.getElementById('acqQuantity')?.value || '');
            fd.append('total_cost', document.getElementById('acqTotalCost')?.value || '');

            // Submit as form POST so PHP populates $_POST
            fetch('api/books.php?action=add', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(result => {
                if (result && result.success) {
                    alert('Book added successfully!');
                    // close modal if present and reset inline form
                    try { closeModal('addBookModal'); } catch (e) {}
                    form.reset();
                    loadStatistics(); // Refresh statistics
                    loadBooksTable();
                } else {
                    alert('Error: ' + (result && result.message ? result.message : 'Failed to add book.'));
                }
            })
            .catch(err => {
                alert('Error: ' + err);
            });
        }

        // Book details modal
        function viewBook(catNo) {
          openBookDetailsModal(catNo, false);
        }
        function editBook(catNo) {
          openBookDetailsModal(catNo, true);
        }
    function openBookDetailsModal(catNo, editable) {
            const modalEl = document.getElementById('bookDetailsModal');
            openModal('bookDetailsModal');
            // store context so other functions (generateHoldingQr) can reload correctly
            if (modalEl) {
                modalEl.setAttribute('data-catno', String(catNo));
                modalEl.setAttribute('data-editable', editable ? '1' : '0');
            }
            document.getElementById('bookDetailsModalTitle').textContent = editable ? 'Edit Book' : 'Book Details';
            document.getElementById('bookDetailsModalBody').innerHTML = '<div style="text-align:center; color:#6c757d; padding:30px;"><i class="fas fa-spinner fa-spin" style="font-size:28px;"></i><p>Loading book details...</p></div>';
            document.getElementById('bookDetailsModalFooter').style.display = 'flex';
            document.getElementById('saveBookEditBtn').style.display = editable ? 'inline-block' : 'none';
                                        console.log('openBookDetailsModal called with catNo:', catNo, 'editable:', editable);
                    fetch(`api/books.php?action=get&catNo=${encodeURIComponent(catNo)}`)
                        .then(res => {
                            if (!res.ok) throw new Error('API error: ' + res.status);
                            return res.json();
                        })
                        .then(result => {
                            if (result.success && result.data) {
                                const book = result.data;
                                console.log('[Book Modal] Loaded book:', book);
                                // If all main fields are empty, show raw JSON for debug
                                if (!book.Title && !book.Author1 && !book.Publisher) {
                                    document.getElementById('bookDetailsModalBody').innerHTML = `<div style='color:#dc3545; padding:20px;'>No data found for this book.<br><pre>${JSON.stringify(book, null, 2)}</pre></div>`;
                                } else {
                                    try {
                                        let html = `<form id='editBookForm' style='width:100%;'>
                                            <div class='form-row'>
                                                <div class='form-group-modal'><label>Title</label><input type='text' name='Title' value='${escapeHtml(book.Title)}' ${editable ? '' : 'readonly'}></div>
                                                <div class='form-group-modal'><label>Sub Title</label><input type='text' name='SubTitle' value='${escapeHtml(book.SubTitle)}' ${editable ? '' : 'readonly'}></div>
                                            </div>
                                            <div class='form-row'>
                                                <div class='form-group-modal'><label>Author 1</label><input type='text' name='Author1' value='${escapeHtml(book.Author1)}' ${editable ? '' : 'readonly'}></div>
                                                <div class='form-group-modal'><label>Author 2</label><input type='text' name='Author2' value='${escapeHtml(book.Author2)}' ${editable ? '' : 'readonly'}></div>
                                                <div class='form-group-modal'><label>Author 3</label><input type='text' name='Author3' value='${escapeHtml(book.Author3)}' ${editable ? '' : 'readonly'}></div>
                                            </div>
                                            <div class='form-row'>
                                                <div class='form-group-modal'><label>Publisher</label><input type='text' name='Publisher' value='${escapeHtml(book.Publisher)}' ${editable ? '' : 'readonly'}></div>
                                                <div class='form-group-modal'><label>Year</label><input type='number' name='Year' value='${escapeHtml(book.Year)}' ${editable ? '' : 'readonly'}></div>
                                                <div class='form-group-modal'><label>ISBN</label><input type='text' name='ISBN' value='${escapeHtml(book.ISBN)}' ${editable ? '' : 'readonly'}></div>
                                                <div class='form-group-modal'><label>Edition</label><input type='text' name='Edition' value='${escapeHtml(book.Edition)}' ${editable ? '' : 'readonly'}></div>
                                            </div>
                                            <div class='form-row'>
                                                <div class='form-group-modal'><label>Language</label><input type='text' name='Language' value='${escapeHtml(book.Language)}' ${editable ? '' : 'readonly'}></div>
                                                <div class='form-group-modal'><label>Subject</label><input type='text' name='Subject' value='${escapeHtml(book.Subject)}' ${editable ? '' : 'readonly'}></div>
                                                <div class='form-group-modal'><label>Pages</label><input type='number' name='Pages' value='${escapeHtml(book.Pages)}' ${editable ? '' : 'readonly'}></div>
                                            </div>
                                            <div class='form-row'>
                                                <div class='form-group-modal'><label>Cat No</label><input type='text' name='CatNo' value='${escapeHtml(book.CatNo)}' readonly></div>
                                            </div>
                                        </form>`;

                                        // Holdings table with inline edit
                                        let holdingsHtml = `<div style='margin-top:14px;'><h4>Holdings <button type='button' class='btn btn-sm btn-success' onclick='addNewHolding(${book.CatNo})' style='float:right;'><i class='fas fa-plus'></i> Add Copy</button></h4><table style='width:100%; border-collapse:collapse;'><thead><tr><th style='text-align:left; padding:6px;'>AccNo</th><th style='text-align:left; padding:6px;'>Status</th><th style='text-align:left; padding:6px;'>Location</th><th style='text-align:left; padding:6px;'>Section</th><th style='text-align:left; padding:6px;'>QR</th><th style='text-align:left; padding:6px;'>Actions</th></tr></thead><tbody>`;
                                        if (Array.isArray(book.holdings) && book.holdings.length) {
                                            book.holdings.forEach(function(h, idx) {
                                                    const safeAcc = escapeHtml(h.AccNo || '');
                                                    const qrBase64 = h.QrCodeBase64 || null;
                                                    const qrPath = h.QRCode || null;
                                                    const holdingId = 'holding_' + idx;
                                                    
                                                    holdingsHtml += `<tr id='${holdingId}' style='border-top:1px solid #eee;' data-accno='${safeAcc}' data-original-accno='${safeAcc}'>
                                                        <td style='padding:6px;'>
                                                            <input type='text' class='holding-accno-input' data-original='${safeAcc}' value='${safeAcc}' 
                                                                   style='width:100%; padding:4px 8px; border:1px solid #ddd; border-radius:4px; font-weight:bold;' 
                                                                   title='Edit AccNo (unique identifier)'>
                                                            <small style='color:#6c757d; font-size:10px;'>Original: ${safeAcc}</small>
                                                        </td>
                                                        <td style='padding:6px;'>
                                                            <select class='holding-status-select' data-accno='${safeAcc}' style='padding:4px 8px; border:1px solid #ddd; border-radius:4px;'>
                                                                <option value='Available' ${h.Status === 'Available' ? 'selected' : ''}>Available</option>
                                                                <option value='Issued' ${h.Status === 'Issued' ? 'selected' : ''}>Issued</option>
                                                                <option value='Reserved' ${h.Status === 'Reserved' ? 'selected' : ''}>Reserved</option>
                                                                <option value='Damaged' ${h.Status === 'Damaged' ? 'selected' : ''}>Damaged</option>
                                                                <option value='Lost' ${h.Status === 'Lost' ? 'selected' : ''}>Lost</option>
                                                                <option value='Repair' ${h.Status === 'Repair' ? 'selected' : ''}>Repair</option>
                                                            </select>
                                                        </td>
                                                        <td style='padding:6px;'><input type='text' class='holding-location-input' data-accno='${safeAcc}' value='${escapeHtml(h.Location || '')}' style='width:100%; padding:4px 8px; border:1px solid #ddd; border-radius:4px;'></td>
                                                        <td style='padding:6px;'><input type='text' class='holding-section-input' data-accno='${safeAcc}' value='${escapeHtml(h.Section || '')}' style='width:100%; padding:4px 8px; border:1px solid #ddd; border-radius:4px;'></td>
                                                        <td style='padding:6px;'>`;
                                                    if (qrBase64) {
                                                        const dataUri = 'data:image/png;base64,' + qrBase64;
                                                        holdingsHtml += `<img class='holding-qr-thumb' data-full='${dataUri}' src='${dataUri}' alt='QR' style='width:48px;height:48px;object-fit:contain;border:1px solid #ddd;padding:2px;cursor:pointer;'> <a href='${dataUri}' download='qr_${safeAcc}.png' class='btn btn-sm' style='margin-left:4px; font-size:11px;'>↓</a>`;
                                                    } else if (qrPath) {
                                                        const streamUrl = `api/books.php?action=qr&accNo=${encodeURIComponent(h.AccNo)}`;
                                                        holdingsHtml += `<img class='holding-qr-thumb' data-full='${streamUrl}' src='${streamUrl}' alt='QR' style='width:48px;height:48px;object-fit:contain;border:1px solid #ddd;padding:2px;cursor:pointer;'> <a href='${streamUrl}' class='btn btn-sm' style='margin-left:4px; font-size:11px;' download>↓</a>`;
                                                    } else {
                                                        const btnId = 'generate-qr-' + safeAcc;
                                                        const statusId = 'qr_status_' + safeAcc;
                                                        holdingsHtml += `<button type='button' data-accno='${escapeHtml(h.AccNo)}' data-catno='${escapeHtml(book.CatNo)}' data-copy='${idx+1}' class='btn btn-sm generate-qr-btn' id='${btnId}' style='font-size:11px;'>Gen QR</button> <span id='${statusId}' class='qr-status' data-accno='${escapeHtml(h.AccNo)}'></span>`;
                                                    }
                                                    holdingsHtml += `</td>
                                                        <td style='padding:6px;'>
                                                            <button type='button' class='btn btn-sm btn-primary save-holding-btn' data-accno='${safeAcc}' title='Save changes'><i class='fas fa-save'></i></button>
                                                            <button type='button' class='btn btn-sm btn-danger delete-holding-btn' data-accno='${safeAcc}' title='Delete holding'><i class='fas fa-trash'></i></button>
                                                        </td>
                                                    </tr>`;
                                                });
                                        } else {
                                            holdingsHtml += `<tr><td colspan='6' style='padding:6px;color:#666;'>No holdings found.</td></tr>`;
                                        }
                                        holdingsHtml += `</tbody></table></div>`;
                                        document.getElementById('bookDetailsModalBody').innerHTML = html + holdingsHtml;
                                    } catch (e) {
                                        document.getElementById('bookDetailsModalBody').innerHTML = `<div style='color:#dc3545; padding:20px;'>Error rendering form: ${e.message}<br><pre>${JSON.stringify(book, null, 2)}</pre></div>`;
                                    }
                                }
                            } else {
                                let msg = 'Book not found.';
                                if (result && result.message) msg += ' ' + result.message;
                                document.getElementById('bookDetailsModalBody').innerHTML = `<div style=\"color:#dc3545; text-align:center; padding:30px;\"><i class=\"fas fa-exclamation-triangle\"></i> ${msg}</div>`;
                            }
                            document.getElementById('bookDetailsModalFooter').style.display = 'block';
                        })
                        .catch(err => {
                            document.getElementById('bookDetailsModalBody').innerHTML = `<div style=\"color:#dc3545; text-align:center; padding:30px;\"><i class=\"fas fa-exclamation-triangle\"></i> Error loading book details: ${err.message}</div>`;
                            document.getElementById('bookDetailsModalFooter').style.display = 'block';
                        });
                    // Store CatNo for save
                    document.getElementById('bookDetailsModal').setAttribute('data-catno', catNo);
                    document.getElementById('bookDetailsModal').setAttribute('data-editable', editable ? '1' : '0');
                }
        function saveBookEdit() {
          const modal = document.getElementById('bookDetailsModal');
          const catNo = modal.getAttribute('data-catno');
          const form = document.getElementById('editBookForm');
          const formData = new FormData(form);
          formData.append('CatNo', catNo);
          fetch('api/books.php?action=update', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(result => {
            if (result.success) {
              alert('Book updated successfully!');
              closeModal('bookDetailsModal');
              loadStatistics(); // Refresh statistics
              loadBooksTable();
            } else {
              alert('Failed to update book.');
            }
          });
        }
        function escapeHtml(text) {
                    if (typeof text !== 'string' && typeof text !== 'number') return '';
                    return String(text).replace(/[&<>"']/g, function (c) {
                        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];
                    });
        }
    // Lightbox handlers
    function onQrThumbClick(e){
        e.stopPropagation();
        const src = e.currentTarget.getAttribute('data-full') || e.currentTarget.src;
        openQrLightbox(src);
    }

    function openQrLightbox(src){
        const lb = document.getElementById('qrLightbox');
        const img = document.getElementById('qrLightboxImg');
        img.src = src;
        lb.classList.add('open');
    }

    function closeQrLightbox(e){
        if(e && e.target && (e.target.id === 'qrLightbox' || e.target.classList.contains('qr-lightbox-close'))){
            document.getElementById('qrLightbox').classList.remove('open');
            document.getElementById('qrLightboxImg').src = '';
        }
    }

    // Delegated handlers: handle clicks on generate buttons and QR thumbnails
    document.addEventListener('click', function(e){
        // Generate QR button
        const gen = e.target.closest && e.target.closest('.generate-qr-btn');
        if (gen) {
            e.preventDefault();
            const accNo = gen.getAttribute('data-accno');
            const catNo = gen.getAttribute('data-catno');
            const copyIndex = gen.getAttribute('data-copy');
            if (accNo) generateHoldingQr(accNo, catNo, copyIndex);
            return;
        }

        // Thumbnail click (delegated) - allow clicks on img or its container
        const thumb = e.target.closest && e.target.closest('.holding-qr-thumb');
        if (thumb) {
            e.preventDefault();
            const src = thumb.getAttribute('data-full') || thumb.src;
            if (src) openQrLightbox(src);
            return;
        }
    });

    // Generate QR for a holding with spinner and immediate lightbox preview
    function generateHoldingQr(accNo, catNo, copyIndex) {
        // find the button and status span using data-accno
        const btn = document.querySelector(".generate-qr-btn[data-accno='" + CSS.escape(accNo) + "']");
        const statusSpan = document.querySelector(".qr-status[data-accno='" + CSS.escape(accNo) + "']");
        if (statusSpan) statusSpan.textContent = '';
        // show spinner next to button
        let spinner = null;
        if (btn) {
            btn.disabled = true;
            spinner = document.createElement('span');
            spinner.className = 'inline-spinner';
            btn.insertAdjacentElement('afterend', spinner);
        }

        const payload = new FormData();
        payload.append('AccNo', accNo);
        payload.append('CatNo', catNo);
        payload.append('CopyIndex', copyIndex);

        fetch('api/books.php?action=generate-qr', {
            method: 'POST',
            body: payload
        })
        .then(res => res.json())
        .then(result => {
            if (btn) btn.disabled = false;
            if (spinner) spinner.remove();
            if (result && result.success) {
                const imgData = result.qrBase64 ? ('data:image/png;base64,' + result.qrBase64) : null;
                if (imgData) {
                    // show immediate preview in lightbox
                    openQrLightbox(imgData);
                    // update status span to include a small thumbnail + download link
                    if (statusSpan) {
                        statusSpan.innerHTML = `<img src='${imgData}' style='width:64px;height:64px;border:1px solid #ddd;padding:4px;vertical-align:middle;margin-right:8px;'> <a href='${imgData}' download='qr_${accNo}.png' class='btn btn-sm btn-success'>Download</a>`;
                    }
                }
                // refresh modal so the holdings show embedded blob next time
                const modal = document.getElementById('bookDetailsModal');
                const cat = modal.getAttribute('data-catno');
                const editable = modal.getAttribute('data-editable') === '1';
                setTimeout(()=> openBookDetailsModal(cat, editable), 350);
            } else {
                if (statusSpan) statusSpan.textContent = 'Failed';
                alert('QR generation failed: ' + (result && result.error ? result.error : 'unknown'));
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            const sp = document.getElementById('spinner-' + accNo);
            if (sp) sp.remove();
            if (statusSpan) statusSpan.textContent = 'Error';
            alert('Error generating QR: ' + err);
        });
    }

    // Holding management handlers
    document.addEventListener('click', function(e) {
        // Save holding button
        const saveBtn = e.target.closest('.save-holding-btn');
        if (saveBtn) {
            e.preventDefault();
            const accNo = saveBtn.getAttribute('data-accno');
            saveHoldingChanges(accNo);
            return;
        }

        // Delete holding button
        const delBtn = e.target.closest('.delete-holding-btn');
        if (delBtn) {
            e.preventDefault();
            const accNo = delBtn.getAttribute('data-accno');
            if (confirm(`Are you sure you want to delete holding ${accNo}?`)) {
                deleteHolding(accNo);
            }
            return;
        }

        // Status select change
        if (e.target.classList.contains('holding-status-select')) {
            e.target.style.borderColor = '#cfac69';
        }
    });

    function saveHoldingChanges(accNo) {
        const row = document.querySelector(`tr[data-accno="${accNo}"]`);
        if (!row) return;

        const originalAccNo = row.getAttribute('data-original-accno') || accNo;
        const newAccNo = row.querySelector('.holding-accno-input').value.trim();
        const status = row.querySelector('.holding-status-select').value;
        const location = row.querySelector('.holding-location-input').value;
        const section = row.querySelector('.holding-section-input').value;

        if (!newAccNo) {
            alert('AccNo cannot be empty!');
            return;
        }

        const saveBtn = row.querySelector('.save-holding-btn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Prepare update payload
        const updates = {
            originalAccNo: originalAccNo,
            newAccNo: newAccNo,
            status: status,
            location: location,
            section: section
        };

        // Update holding via API
        fetch('api/books.php?action=update-holding', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(updates)
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                saveBtn.innerHTML = '<i class="fas fa-check"></i>';
                saveBtn.style.backgroundColor = '#28a745';
                
                // Update data attributes if AccNo changed
                if (newAccNo !== originalAccNo) {
                    row.setAttribute('data-accno', newAccNo);
                    row.setAttribute('data-original-accno', newAccNo);
                    row.querySelector('.holding-accno-input').setAttribute('data-original', newAccNo);
                    // Update all data-accno attributes in the row
                    row.querySelectorAll('[data-accno]').forEach(el => {
                        if (el.getAttribute('data-accno') === originalAccNo) {
                            el.setAttribute('data-accno', newAccNo);
                        }
                    });
                }
                
                setTimeout(() => {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i>';
                    saveBtn.style.backgroundColor = '';
                    saveBtn.disabled = false;
                    // Refresh statistics
                    loadStatistics();
                    // Refresh modal to show updated data
                    const modal = document.getElementById('bookDetailsModal');
                    const catNo = modal.getAttribute('data-catno');
                    const editable = modal.getAttribute('data-editable') === '1';
                    if (catNo) openBookDetailsModal(catNo, editable);
                }, 1000);
            } else {
                alert('Failed to save changes: ' + (result.message || 'Unknown error'));
                saveBtn.innerHTML = '<i class="fas fa-save"></i>';
                saveBtn.disabled = false;
            }
        })
        .catch(err => {
            alert('Error saving changes: ' + err);
            saveBtn.innerHTML = '<i class="fas fa-save"></i>';
            saveBtn.disabled = false;
        });
    }

    function deleteHolding(accNo) {
        // TODO: Add API endpoint for deleting holdings
        alert('Delete holding feature coming soon. AccNo: ' + accNo);
    }

    function addNewHolding(catNo) {
        const accNo = prompt('Enter Accession Number for new holding:');
        if (!accNo) return;

        fetch('api/books.php?action=add-holding', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                AccNo: accNo,
                CatNo: catNo,
                Status: 'Available',
                Location: '',
                Section: ''
            })
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert('Holding added successfully!');
                // Refresh statistics
                loadStatistics();
                // Refresh modal
                const modal = document.getElementById('bookDetailsModal');
                const editable = modal.getAttribute('data-editable') === '1';
                openBookDetailsModal(catNo, editable);
            } else {
                alert('Failed to add holding: ' + (result.message || 'Unknown error'));
            }
        })
        .catch(err => {
            alert('Error adding holding: ' + err);
        });
    }

    </script>
    </body>
    </html>

<script>
// Basic modal accessibility helpers
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'flex';
    modal.setAttribute('role','dialog');
    modal.setAttribute('aria-modal','true');
    // focus the first focusable element
    const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (focusable) focusable.focus();
    // trap focus minimally
    document.addEventListener('keydown', escClose);
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'none';
    modal.removeAttribute('role');
    modal.removeAttribute('aria-modal');
    document.removeEventListener('keydown', escClose);
}

function escClose(e){
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(m=>{ if (m.style.display==='flex') m.style.display='none'; });
        document.removeEventListener('keydown', escClose);
    }
}

// wire existing buttons if they call openModal/closeModal
document.addEventListener('click', function(e){
    if (e.target.matches('[data-open-modal]')){
        openModal(e.target.getAttribute('data-open-modal'));
    }
});
</script>