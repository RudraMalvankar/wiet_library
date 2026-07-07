<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
    http_response_code(403);
    die('Unauthorized access');
}

$reportDate = date('Y-m-d');

$issuedStmt = $pdo->prepare("\n    SELECT\n        c.CirculationID,\n        c.IssueDate,\n        c.IssueTime,\n        c.DueDate,\n        m.MemberNo,\n        m.MemberName,\n        c.AccNo,\n        b.Title,\n        b.Author1\n    FROM Circulation c\n    INNER JOIN Member m ON c.MemberNo = m.MemberNo\n    INNER JOIN Holding h ON c.AccNo = h.AccNo\n    INNER JOIN Books b ON h.CatNo = b.CatNo\n    WHERE c.IssueDate = ?\n    ORDER BY c.IssueTime DESC, c.CirculationID DESC\n");
$issuedStmt->execute([$reportDate]);
$issuedRows = $issuedStmt->fetchAll(PDO::FETCH_ASSOC);
$todayIssued = count($issuedRows);

$returnedStmt = $pdo->prepare("\n    SELECT\n        r.ReturnID,\n        r.ReturnDate,\n        r.ReturnTime,\n        r.MemberNo,\n        m.MemberName,\n        r.AccNo,\n        b.Title,\n        b.Author1,\n        r.FineAmount,\n        r.FinePaid,\n        r.`Condition`\n    FROM `Return` r\n    INNER JOIN Member m ON r.MemberNo = m.MemberNo\n    INNER JOIN Holding h ON r.AccNo = h.AccNo\n    INNER JOIN Books b ON h.CatNo = b.CatNo\n    WHERE r.ReturnDate = ?\n    ORDER BY r.ReturnTime DESC, r.ReturnID DESC\n");
$returnedStmt->execute([$reportDate]);
$returnedRows = $returnedStmt->fetchAll(PDO::FETCH_ASSOC);
$todayReturned = count($returnedRows);

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today Library Activity Report</title>
    <style>
        @page { size: A4; margin: 12mm; }
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #1f2937;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .header {
            border-bottom: 3px solid #263c79;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .title {
            margin: 0;
            color: #263c79;
            font-size: 28px;
            font-weight: 800;
        }
        .sub {
            margin: 4px 0 0;
            color: #475569;
            font-size: 14px;
        }
        .meta {
            margin: 16px 0;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            max-width: 600px;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px;
        }
        .meta-label {
            text-transform: uppercase;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }
        .meta-value {
            margin-top: 4px;
            color: #0f172a;
            font-size: 24px;
            font-weight: 800;
        }
        .section-title {
            margin: 24px 0 10px;
            color: #263c79;
            font-size: 18px;
            font-weight: 800;
            border-left: 4px solid #cfac69;
            padding-left: 10px;
        }
        .section-note {
            margin: 0 0 10px;
            color: #64748b;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 12px;
            text-align: left;
            vertical-align: top;
        }
        thead th {
            background: #263c79;
            color: #ffffff;
        }
        tfoot td {
            font-weight: 700;
            background: #eef2ff;
        }
        .actions {
            margin-top: 18px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn {
            border: none;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-print {
            background: #263c79;
            color: #fff;
        }
        .btn-close {
            background: #e2e8f0;
            color: #0f172a;
        }
        .footer {
            margin-top: 16px;
            color: #64748b;
            font-size: 12px;
            text-align: right;
        }
        .no-data {
            text-align: center;
            color: #64748b;
            font-style: italic;
        }
        @media print {
            body { margin: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">WIET Library - Today Activity Report</h1>
        <p class="sub">Watumull Institute of Engineering and Technology</p>
        <p class="sub">Report Date: <?php echo htmlspecialchars(date('d-m-Y', strtotime($reportDate)), ENT_QUOTES, 'UTF-8'); ?> | Generated: <?php echo htmlspecialchars(date('d-m-Y H:i:s'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <div class="meta">
        <div class="meta-box">
            <div class="meta-label">Books Issued Today</div>
            <div class="meta-value"><?php echo $todayIssued; ?></div>
        </div>
        <div class="meta-box">
            <div class="meta-label">Books Returned Today</div>
            <div class="meta-value"><?php echo $todayReturned; ?></div>
        </div>
    </div>

    <div class="section-title">Issued Today (Who Took Which Book)</div>
    <p class="section-note">Transactions issued on <?php echo htmlspecialchars(date('d-m-Y', strtotime($reportDate)), ENT_QUOTES, 'UTF-8'); ?>.</p>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 110px;">Issue Date</th>
                <th style="width: 90px;">Issue Time</th>
                <th style="width: 100px;">Member No</th>
                <th>Member Name</th>
                <th style="width: 100px;">Acc No</th>
                <th>Book Title</th>
                <th>Author</th>
                <th style="width: 110px;">Due Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($issuedRows)): ?>
                <?php $index = 1; ?>
                <?php foreach ($issuedRows as $row): ?>
                    <tr>
                        <td><?php echo $index++; ?></td>
                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime((string)$row['IssueDate'])), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['IssueTime'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['MemberNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['MemberName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['AccNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['Title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['Author1'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime((string)$row['DueDate'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="no-data">No books were issued today.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8">Total Books Issued Today</td>
                <td><?php echo $todayIssued; ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Returned Today (Which Book Came Back)</div>
    <p class="section-note">Transactions returned on <?php echo htmlspecialchars(date('d-m-Y', strtotime($reportDate)), ENT_QUOTES, 'UTF-8'); ?>.</p>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 110px;">Return Date</th>
                <th style="width: 90px;">Return Time</th>
                <th style="width: 100px;">Member No</th>
                <th>Member Name</th>
                <th style="width: 100px;">Acc No</th>
                <th>Book Title</th>
                <th>Author</th>
                <th style="width: 80px;">Fine</th>
                <th style="width: 80px;">Paid</th>
                <th style="width: 90px;">Condition</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($returnedRows)): ?>
                <?php $index = 1; ?>
                <?php foreach ($returnedRows as $row): ?>
                    <tr>
                        <td><?php echo $index++; ?></td>
                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime((string)$row['ReturnDate'])), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['ReturnTime'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['MemberNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['MemberName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['AccNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['Title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['Author1'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo number_format((float)($row['FineAmount'] ?? 0), 2); ?></td>
                        <td><?php echo number_format((float)($row['FinePaid'] ?? 0), 2); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['Condition'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" class="no-data">No books were returned today.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10">Total Books Returned Today</td>
                <td><?php echo $todayReturned; ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">WIET Library Management System</div>

    <div class="actions">
        <button class="btn btn-close" type="button" onclick="window.close();">Close</button>
        <button class="btn btn-print" type="button" onclick="window.print();">Print / Save as PDF</button>
    </div>
</body>
</html>
