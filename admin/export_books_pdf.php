<?php
/**
 * Books Export (Print-to-PDF)
 * Renders a print-friendly HTML report that can be saved as PDF from browser print dialog.
 */

session_start();
require_once '../includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
    die('Unauthorized access');
}

// Fetch each physical copy as its own row using accession number
$sql = "
    SELECT 
        b.CatNo,
        b.Title,
        b.SubTitle,
        b.Author1,
        b.Author2,
        b.Author3,
        b.Publisher,
        b.Year,
        b.Place,
        b.Subject,
        b.Edition,
        h.AccNo,
        h.Status
    FROM Books b
    LEFT JOIN Holding h ON b.CatNo = h.CatNo
    ORDER BY b.Title, h.AccNo
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Serve as HTML so browser can render and print/save as PDF.
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Library Books Catalog</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #263c79;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #263c79;
            margin: 0 0 5px 0;
            font-size: 20pt;
        }
        
        .header h2 {
            color: #cfac69;
            margin: 0;
            font-size: 14pt;
            font-weight: normal;
        }
        
        .meta-info {
            text-align: right;
            margin-bottom: 20px;
            font-size: 9pt;
            color: #666;
        }
        
        .summary-stats {
            background: #f8f9fa;
            border: 2px solid #263c79;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
            display: table;
            width: 100%;
            page-break-inside: avoid;
        }
        
        .summary-stats .stat {
            display: inline-block;
            margin-right: 30px;
        }
        
        .summary-stats .stat-label {
            font-weight: bold;
            color: #263c79;
        }
        
        .summary-stats .stat-value {
            color: #cfac69;
            font-size: 14pt;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
            page-break-inside: auto;
        }
        
        thead {
            background: #263c79;
            color: white;
            display: table-header-group;
        }
        
        th {
            padding: 8px 5px;
            text-align: left;
            font-size: 8.5pt;
            font-weight: bold;
            border: 1px solid #1a2a5a;
            page-break-inside: avoid;
        }
        
        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 8.5pt;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: anywhere;
            page-break-inside: avoid;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e9ecef;
        }
        
        .book-row { page-break-inside: avoid; page-break-after: auto; }
        
        .book-title {
            font-weight: bold;
            color: #263c79;
        }
        
        .availability-good {
            color: #28a745;
            font-weight: bold;
        }
        
        .availability-warning {
            color: #ffc107;
            font-weight: bold;
        }
        
        .availability-danger {
            color: #dc3545;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        @media print {
            html, body {
                width: 210mm;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>WIET Library</h1>
        <h2>Books Catalog - Complete Collection</h2>
    </div>
    
    <div class="meta-info">
        <strong>Report Generated:</strong> <?php echo date('d F Y, H:i:s'); ?><br>
        <strong>Generated by:</strong> <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
    </div>
    
    <div class="summary-stats">
        <div class="stat">
            <span class="stat-label">Total Titles:</span>
            <span class="stat-value"><?php echo count(array_unique(array_column($books, 'CatNo'))); ?></span>
        </div>
        <div class="stat">
            <span class="stat-label">Total Copies:</span>
            <span class="stat-value"><?php 
                $totalCopies = 0;
                foreach ($books as $book) {
                    if (!empty($book['AccNo'])) {
                        $totalCopies++;
                    }
                }
                echo $totalCopies;
            ?></span>
        </div>
        <div class="stat">
            <span class="stat-label">Available:</span>
            <span class="stat-value"><?php 
                $availableCopies = 0;
                foreach ($books as $book) {
                    if (($book['Status'] ?? '') === 'Available') {
                        $availableCopies++;
                    }
                }
                echo $availableCopies;
            ?></span>
        </div>
        <div class="stat">
            <span class="stat-label">Issued:</span>
            <span class="stat-value"><?php 
                $issuedCopies = 0;
                foreach ($books as $book) {
                    if (($book['Status'] ?? '') === 'Issued') {
                        $issuedCopies++;
                    }
                }
                echo $issuedCopies;
            ?></span>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Acc No.</th>
                <th style="width: 7%;">Cat No.</th>
                <th style="width: 27%;">Title & Author</th>
                <th style="width: 16%;">Publisher & Year</th>
                <th style="width: 13%;">Subject</th>
                <th style="width: 8%;">Edition</th>
                <th style="width: 10%;">Copy Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): 
                $copyStatus = $book['Status'] ?? 'N/A';
                if ($copyStatus === 'Available') {
                    $statusClass = 'availability-good';
                } elseif ($copyStatus === 'Issued') {
                    $statusClass = 'availability-warning';
                } else {
                    $statusClass = 'availability-danger';
                }
            ?>
            <tr class="book-row">
                <td><?php echo htmlspecialchars($book['AccNo'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($book['CatNo']); ?></td>
                <td>
                    <div class="book-title"><?php echo htmlspecialchars($book['Title']); ?></div>
                    <?php if (!empty($book['SubTitle'])): ?>
                        <small style="color: #666;"><?php echo htmlspecialchars($book['SubTitle']); ?></small><br>
                    <?php endif; ?>
                    <small style="color: #555;">
                        <?php 
                        $authors = array_filter([
                            $book['Author1'] ?? '',
                            $book['Author2'] ?? '',
                            $book['Author3'] ?? ''
                        ]);
                        echo htmlspecialchars(implode(', ', $authors));
                        ?>
                    </small>
                </td>
                <td>
                    <?php echo htmlspecialchars($book['Publisher'] ?? 'N/A'); ?><br>
                    <small style="color: #666;">
                        <?php echo htmlspecialchars($book['Year'] ?? 'N/A'); ?>
                        <?php if (!empty($book['Place'])): ?>
                            - <?php echo htmlspecialchars($book['Place']); ?>
                        <?php endif; ?>
                    </small>
                </td>
                <td><?php echo htmlspecialchars($book['Subject'] ?? 'General'); ?></td>
                <td><?php echo htmlspecialchars($book['Edition'] ?? 'N/A'); ?></td>
                <td class="<?php echo $statusClass; ?>" style="text-align: center;">
                    <?php echo htmlspecialchars($copyStatus); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p><strong>WIET Library Management System</strong></p>
        <p>This is an automatically generated report. All data is subject to real-time changes.</p>
                    <p>© <?php echo date('Y'); ?> - Watumull Institute of Engineering and Technology</p>
    </div>
    
    <script>
        // Auto-trigger print dialog for PDF generation
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
