<?php
// books_add.php - Admin: Add Book flow
// Inserts into Books, Acquisition, and Holding tables. Generates AccNo, BarCode, QR, and stores file path in Holding.BarCode.
// UI: Use existing form fields as per requirements.md

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect book metadata from POST
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
        'CatNo' => null // will be set after insert
    ];

    // Insert into Books
    $stmt = $pdo->prepare("INSERT INTO Books (Title, Author1, Author2, Author3, Publisher, Year, ISBN, Edition, Language, Subject, Keywords, Pages, Price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $bookData['Title'], $bookData['Author1'], $bookData['Author2'], $bookData['Author3'], $bookData['Publisher'], $bookData['Year'], $bookData['ISBN'], $bookData['Edition'], $bookData['Language'], $bookData['Subject'], $bookData['Keywords'], $bookData['Pages'], $bookData['Price']
    ]);
    $bookData['CatNo'] = $pdo->lastInsertId();

    // Acquisition details
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

    // Create Holding records for each copy
    $numCopies = (int)$_POST['num_copies'];
    for ($i = 1; $i <= $numCopies; $i++) {
        $accNo = generateAccNo($bookData['CatNo'], $i); // function to generate unique AccNo
        $barcodePath = generateBarcode($accNo); // function to generate barcode image and return file path
        $qrPath = generateQR($accNo); // function to generate QR image and return file path
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

// Helper generators if not provided by includes/functions.php
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
        if (!$baseDir) {
            $baseDir = __DIR__ . '/../storage';
        }
        $dir = $baseDir . '/barcodes';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $filename = $dir . '/barcode_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$text) . '.png';

        if (function_exists('imagecreatetruecolor')) {
            $width = 480;
            $height = 140;
            $img = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefilledrectangle($img, 0, 0, $width, $height, $white);

            // Simple pseudo-barcode based on hash of text
            $hash = md5((string)$text);
            $x = 10;
            $maxX = $width - 10;
            for ($i = 0; $i < strlen($hash) && $x < $maxX; $i++) {
                $val = hexdec($hash[$i]);
                $barWidth = max(1, ($val % 4) + 1);
                $barHeight = 100 + ($val % 20);
                imagefilledrectangle($img, $x, 20, $x + $barWidth, 20 + $barHeight, $black);
                $x += $barWidth + 2;
            }

            // Human readable text
            if (function_exists('imagestring')) {
                imagestring($img, 4, 10, $height - 20, (string)$text, $black);
            }
            imagepng($img, $filename);
            imagedestroy($img);
        } else {
            // Fallback: write plain text file to indicate path
            file_put_contents($filename . '.txt', (string)$text);
            $filename .= '.txt';
        }

        return $filename;
    }
}

if (!function_exists('generateQR')) {
    function generateQR($text) {
        $baseDir = realpath(__DIR__ . '/../storage');
        if (!$baseDir) {
            $baseDir = __DIR__ . '/../storage';
        }
        $dir = $baseDir . '/qrcodes';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $filename = $dir . '/qr_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$text) . '.png';

        if (function_exists('imagecreatetruecolor')) {
            $size = 320;
            $img = imagecreatetruecolor($size, $size);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefilledrectangle($img, 0, 0, $size, $size, $white);

            // Placeholder "QR" pattern: draw squares based on hash bits
            $hash = md5((string)$text, true); // 16 bytes
            $grid = 21; // typical small QR grid
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

            // Label
            if (function_exists('imagestring')) {
                imagestring($img, 3, 10, $size - 18, (string)$text, $black);
            }
            imagepng($img, $filename);
            imagedestroy($img);
        } else {
            // Fallback: write plain text file to indicate path
            file_put_contents($filename . '.txt', (string)$text);
            $filename .= '.txt';
        }

        return $filename;
    }
}

// End of helpers

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Management & Add Book</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .form-section { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 200px; display: flex; flex-direction: column; }
        label { font-weight: 600; color: #263c79; margin-bottom: 5px; font-size: 14px; }
        input, select, textarea { padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .required { color: #dc3545; }
        .form-actions { margin-top: 20px; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #cfac69; }
        .stat-number { font-size: 32px; font-weight: 700; color: #263c79; margin-bottom: 5px; }
        .stat-label { color: #6c757d; font-size: 14px; font-weight: 500; }
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 20px; }
        .page-link { padding: 8px 12px; border: 1px solid #ddd; color: #263c79; text-decoration: none; border-radius: 4px; }
        .page-link:hover, .page-link.active { background-color: #263c79; color: white; }
        .action-links { display: flex; gap: 8px; }
        .action-links a { padding: 4px 8px; border-radius: 3px; text-decoration: none; font-size: 12px; font-weight: 500; }
        .btn-view { background-color: #17a2b8; color: white; }
        .btn-edit { background-color: #ffc107; color: #212529; }
        .btn-delete { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <h2>Add New Book</h2>
    <form method="POST" id="addBookForm">
        <div class="form-section">
            <div class="form-row">
                <div class="form-group">
                    <label>Title <span class="required">*</span></label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Author 1 <span class="required">*</span></label>
                    <input type="text" name="author1" required>
                </div>
                <div class="form-group">
                    <label>Author 2</label>
                    <input type="text" name="author2">
                </div>
                <div class="form-group">
                    <label>Author 3</label>
                    <input type="text" name="author3">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Publisher <span class="required">*</span></label>
                    <input type="text" name="publisher" required>
                </div>
                <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="year" min="1900" max="2030">
                </div>
                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn">
                </div>
                <div class="form-group">
                    <label>Edition</label>
                    <input type="text" name="edition">
                </div>
                <div class="form-group">
                    <label>Language</label>
                    <input type="text" name="language" value="English">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject">
                </div>
                <div class="form-group">
                    <label>Keywords</label>
                    <input type="text" name="keywords">
                </div>
                <div class="form-group">
                    <label>Pages</label>
                    <input type="number" name="pages" min="1">
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" min="0" step="0.01">
                </div>
            </div>
        </div>
        <div class="form-section">
            <h4>Acquisition Details</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Supplier</label>
                    <input type="text" name="supplier">
                </div>
                <div class="form-group">
                    <label>Invoice No</label>
                    <input type="text" name="invoice_no">
                </div>
                <div class="form-group">
                    <label>Invoice Date</label>
                    <input type="date" name="invoice_date">
                </div>
                <div class="form-group">
                    <label>Order No</label>
                    <input type="text" name="order_no">
                </div>
                <div class="form-group">
                    <label>Order Date</label>
                    <input type="date" name="order_date">
                </div>
                <div class="form-group">
                    <label>Received Date</label>
                    <input type="date" name="received_date">
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" min="1" value="1">
                </div>
                <div class="form-group">
                    <label>Total Cost</label>
                    <input type="number" name="total_cost" min="0" step="0.01">
                </div>
            </div>
        </div>
        <div class="form-section">
            <h4>Holdings (Copies)</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Number of Copies</label>
                    <input type="number" name="num_copies" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label>Book No</label>
                    <input type="text" name="book_no">
                </div>
                <div class="form-group">
                    <label>Accession Date</label>
                    <input type="date" name="acc_date">
                </div>
                <div class="form-group">
                    <label>Class No</label>
                    <input type="text" name="class_no">
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location">
                </div>
                <div class="form-group">
                    <label>Section</label>
                    <input type="text" name="section">
                </div>
                <div class="form-group">
                    <label>Collection</label>
                    <input type="text" name="collection">
                </div>
                <div class="form-group">
                    <label>Binding</label>
                    <input type="text" name="binding">
                </div>
                <div class="form-group">
                    <label>Remarks</label>
                    <input type="text" name="remarks">
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit">Add Book</button>
        </div>
    </form>

    <h2>Books Catalog</h2>
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
    <div id="booksTableContainer"></div>
    <script>
    // --- Book Management JS (from books-management.php) ---
    let booksPage = 1;
    let booksPageSize = 20;
    let booksTotal = 0;
    async function loadBooksTable(page = 1) {
        booksPage = page;
        document.getElementById('booksTableContainer').innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #263c79;"></i><p>Loading books...</p></div>';
        try {
            const response = await fetch(`api/books.php?action=list&page=${booksPage}&pageSize=${booksPageSize}`);
            const result = await response.json();
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
            document.getElementById('booksTableContainer').innerHTML = tableHTML;
        } catch (error) {
            console.error('Error loading books:', error);
            document.getElementById('booksTableContainer').innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i><p>Error loading books. Please try again.</p></div>';
        }
    }
    // Statistics
    function loadStatistics() {
        fetch('api/books.php?action=list&page=1&pageSize=1')
            .then(res => res.json())
            .then(result => {
                if (!result.success) return;
                fetch('api/books.php?action=list&page=1&pageSize=10000')
                    .then(res2 => res2.json())
                    .then(data => {
                        let totalBooks = data.data.length;
                        let totalCopies = 0;
                        let availableCopies = 0;
                        let issuedCopies = 0;
                        data.data.forEach(book => {
                            totalCopies += parseInt(book.TotalCopies || 0);
                            availableCopies += parseInt(book.AvailableCopies || 0);
                            issuedCopies += parseInt(book.IssuedCopies || 0);
                        });
                        document.getElementById('totalBooks').textContent = totalBooks.toLocaleString();
                        document.getElementById('totalCopies').textContent = totalCopies.toLocaleString();
                        document.getElementById('availableCopies').textContent = availableCopies.toLocaleString();
                        document.getElementById('issuedCopies').textContent = issuedCopies.toLocaleString();
                    });
            });
    }
    // Book actions (view/edit/delete)
    function viewBook(catNo) { alert('View details coming soon.'); }
    function editBook(catNo) { alert('Edit book coming soon.'); }
    function deleteBook(catNo) {
        if (confirm(`Are you sure you want to delete book with Cat No: ${catNo}?`)) {
            fetch('api/books.php?action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ CatNo: catNo })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    alert('Book deleted successfully!');
                    loadBooksTable();
                } else {
                    alert('Error: ' + (result.message || 'Failed to delete book.'));
                }
            })
            .catch(err => { alert('Error: ' + err); });
        }
    }
    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        loadBooksTable();
        loadStatistics();
    });
    </script>
</body>
</html>