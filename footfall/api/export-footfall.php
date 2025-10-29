<?php
// Export Footfall Data API
header('Content-Type: application/json');
require_once '../../includes/db_connect.php';

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$format = $_GET['format'] ?? 'json'; // json, csv, excel

try {
    $stmt = $pdo->prepare("
        SELECT 
            f.FootfallID as 'Record ID',
            CONCAT('M', LPAD(f.MemberNo, 7, '0')) as 'Member Number',
            m.MemberName as 'Name',
            s.Branch as 'Branch',
            s.CourseName as 'Course',
            DATE_FORMAT(f.EntryTime, '%Y-%m-%d %H:%i:%s') as 'Entry Time',
            DATE_FORMAT(f.ExitTime, '%Y-%m-%d %H:%i:%s') as 'Exit Time',
            CONCAT(
                FLOOR(f.Duration / 60), 'h ',
                f.Duration % 60, 'm'
            ) as 'Duration',
            f.Purpose,
            f.EntryMethod as 'Entry Method',
            f.Status
        FROM footfall f
        INNER JOIN Member m ON f.MemberNo = m.MemberNo
        LEFT JOIN Student s ON f.MemberNo = s.MemberNo
        WHERE DATE(f.EntryTime) BETWEEN :date_from AND :date_to
        ORDER BY f.EntryTime DESC
    ");
    $stmt->execute(['date_from' => $dateFrom, 'date_to' => $dateTo]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="footfall_report_' . $dateFrom . '_to_' . $dateTo . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        if (!empty($records)) {
            fputcsv($output, array_keys($records[0]));
        }
        
        // Data
        foreach ($records as $record) {
            fputcsv($output, $record);
        }
        
        fclose($output);
        exit;
    } else {
        // JSON format
        echo json_encode([
            'success' => true,
            'records' => $records,
            'count' => count($records),
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Export error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>

