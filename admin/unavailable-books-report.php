<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['AdminID'])) {
    http_response_code(403);
    die('Unauthorized access');
}

$reportDate = date('Y-m-d');

$stmt = $pdo->query("\n    SELECT\n        h.AccNo,\n        b.CatNo,\n        b.Title,\n        b.Author1,\n        h.Status,\n        h.Location,\n        h.`Section`,\n        h.ExpectedAvailableDate,\n        h.AvailabilityNote,\n        c.CirculationID,\n        c.IssueDate,\n        c.DueDate,\n        m.MemberNo,\n        m.MemberName\n    FROM Holding h\n    INNER JOIN Books b ON h.CatNo = b.CatNo\n    LEFT JOIN Circulation c ON c.AccNo = h.AccNo AND c.Status = 'Active'\n    LEFT JOIN Member m ON m.MemberNo = c.MemberNo\n    WHERE h.Status <> 'Available'\n    ORDER BY\n        CASE h.Status\n            WHEN 'Issued' THEN 1\n            WHEN 'Reserved' THEN 2\n            WHEN 'Unavailable' THEN 3\n            WHEN 'Repair' THEN 4\n            WHEN 'Lost' THEN 5\n            WHEN 'Permanently Lost' THEN 6\n            WHEN 'Damaged' THEN 7\n            WHEN 'Dead Stock' THEN 8\n            ELSE 99\n        END,\n        b.Title ASC,\n        h.AccNo ASC\n");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalNotAvailable = count($rows);
$issuedOut = 0;
$physicalNotInLibrary = 0;
$statusCounts = [];

foreach ($rows as $row) {
    $status = (string)($row['Status'] ?? 'Unknown');
    if (!isset($statusCounts[$status])) {
        $statusCounts[$status] = 0;
    }
    $statusCounts[$status]++;

    if ($status === 'Issued') {
        $issuedOut++;
    }

    if (in_array($status, ['Unavailable', 'Repair', 'Reserved', 'Lost', 'Permanently Lost', 'Damaged', 'Dead Stock'], true)) {
        $physicalNotInLibrary++;
    }
}

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unavailable Books Report</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #1f2937;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .header {
            border-bottom: 3px solid #263c79;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .title {
            margin: 0;
            color: #263c79;
            font-size: 26px;
            font-weight: 800;
        }
        .sub {
            margin: 4px 0 0;
            color: #475569;
            font-size: 13px;
        }
        .meta {
            margin: 14px 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px;
        }
        .meta-label {
            text-transform: uppercase;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
        }
        .meta-value {
            margin-top: 3px;
            color: #0f172a;
            font-size: 22px;
            font-weight: 800;
        }
        .section-title {
            margin: 18px 0 8px;
            color: #263c79;
            font-size: 17px;
            font-weight: 800;
            border-left: 4px solid #cfac69;
            padding-left: 10px;
        }
        .section-note {
            margin: 0 0 8px;
            color: #64748b;
            font-size: 12px;
        }
        .status-strip {
            margin: 8px 0 12px;
            font-size: 12px;
            color: #334155;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
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
        .no-data {
            text-align: center;
            color: #64748b;
            font-style: italic;
        }
        .actions {
            margin-top: 14px;
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
            margin-top: 14px;
            color: #64748b;
            font-size: 12px;
            text-align: right;
        }
        @media print {
            body { margin: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">WIET Library - Unavailable Books Report</h1>
        <p class="sub">Watumull Institute of Engineering and Technology</p>
        <p class="sub">Generated: <?php echo htmlspecialchars(date('d-m-Y H:i:s'), ENT_QUOTES, 'UTF-8'); ?> | Report Date: <?php echo htmlspecialchars(date('d-m-Y', strtotime($reportDate)), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <div class="meta">
        <div class="meta-box">
            <div class="meta-label">Total Not Available</div>
            <div class="meta-value"><?php echo $totalNotAvailable; ?></div>
        </div>
        <div class="meta-box">
            <div class="meta-label">Currently Issued (With Members)</div>
            <div class="meta-value"><?php echo $issuedOut; ?></div>
        </div>
        <div class="meta-box">
            <div class="meta-label">Physically Not In Library</div>
            <div class="meta-value"><?php echo $physicalNotInLibrary; ?></div>
        </div>
    </div>

    <div class="section-title">Unavailable / Not-In-Library Books</div>
    <p class="section-note">Shows which books are unavailable, where they are marked, and with whom they are currently issued (if applicable).</p>

    <?php if (!empty($statusCounts)): ?>
        <div class="status-strip">
            <strong>Status Summary:</strong>
            <?php
                $parts = [];
                foreach ($statusCounts as $status => $count) {
                    $parts[] = htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . ': ' . (int)$count;
                }
                echo implode(' | ', $parts);
            ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th style="width: 55px;">#</th>
                <th style="width: 100px;">Acc No</th>
                <th style="width: 80px;">Cat No</th>
                <th>Book Title</th>
                <th style="width: 130px;">Author</th>
                <th style="width: 105px;">Status</th>
                <th style="width: 120px;">Location / Section</th>
                <th style="width: 160px;">With Whom (Member)</th>
                <th style="width: 100px;">Issue Date</th>
                <th style="width: 100px;">Due Date</th>
                <th style="width: 110px;">Expected Available</th>
                <th style="width: 170px;">Note</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)): ?>
                <?php $index = 1; ?>
                <?php foreach ($rows as $row): ?>
                    <?php
                        $location = trim((string)($row['Location'] ?? ''));
                        $section = trim((string)($row['Section'] ?? ''));
                        $whereText = trim($location . ($section !== '' ? ' / ' . $section : ''));
                        if ($whereText === '') { $whereText = '-'; }

                        $withWhom = '-';
                        if (!empty($row['MemberNo']) || !empty($row['MemberName'])) {
                            $withWhom = trim((string)($row['MemberNo'] ?? '')) . ' - ' . trim((string)($row['MemberName'] ?? ''));
                        }
                    ?>
                    <tr>
                        <td><?php echo $index++; ?></td>
                        <td><?php echo htmlspecialchars((string)($row['AccNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['CatNo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['Title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['Author1'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['Status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($whereText, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($withWhom, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo !empty($row['IssueDate']) ? htmlspecialchars(date('d-m-Y', strtotime((string)$row['IssueDate'])), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                        <td><?php echo !empty($row['DueDate']) ? htmlspecialchars(date('d-m-Y', strtotime((string)$row['DueDate'])), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                        <td><?php echo !empty($row['ExpectedAvailableDate']) ? htmlspecialchars(date('d-m-Y', strtotime((string)$row['ExpectedAvailableDate'])), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                        <td><?php echo htmlspecialchars((string)($row['AvailabilityNote'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="12" class="no-data">All books are currently available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="11">Total Not Available Books</td>
                <td><?php echo $totalNotAvailable; ?></td>
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
