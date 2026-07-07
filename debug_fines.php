<?php
require_once "includes/db_connect.php";
$member_no = "2511";
echo "--- (A) Simplified Diagnostic Query ---\n";
$diagnostic_query = "
    SELECT
        c.CirculationID,
        c.DueDate,
        r.CirculationID AS ReturnLinked,
        r.FineAmount AS ReturnFineAmount,
        m.FinePerDay,
        CASE
            WHEN r.CirculationID IS NOT NULL THEN r.FineAmount
            ELSE GREATEST(DATEDIFF(CURDATE(), c.DueDate), 0) * COALESCE(m.FinePerDay, 2)
        END AS CalculatedFine,
        (SELECT COALESCE(SUM(fp.PaidAmount), 0) FROM FinePayments fp WHERE fp.CirculationID = c.CirculationID) AS PaidAmount
    FROM Circulation c
    INNER JOIN Member m ON c.MemberNo = m.MemberNo
    LEFT JOIN `Return` r ON c.CirculationID = r.CirculationID
    WHERE c.MemberNo = :member_no
";
$stmt = $pdo->prepare($diagnostic_query);
$stmt->execute(["member_no" => $member_no]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_pending = 0;
foreach ($results as $row) {
    print_r($row);
    $total_pending += ((float)$row["CalculatedFine"] - (float)$row["PaidAmount"]);
}
echo "Total Calculated Fine Minus Paid: $total_pending\n\n";
echo "--- (B) Exact Query from student/dashboard.php ---\n";
$fines_query = "
    SELECT
        c.CirculationID,
        CASE
            WHEN r.CirculationID IS NOT NULL THEN r.FineAmount
            ELSE GREATEST(DATEDIFF(CURDATE(), c.DueDate), 0) * COALESCE(m.FinePerDay, 2)
        END AS CalculatedFine,
        COALESCE(SUM(fp.PaidAmount), 0) AS PaidAmount
    FROM Circulation c
    INNER JOIN Member m ON c.MemberNo = m.MemberNo
    LEFT JOIN `Return` r ON c.CirculationID = r.CirculationID
    LEFT JOIN FinePayments fp ON c.CirculationID = fp.CirculationID
    WHERE c.MemberNo = :member_no
    AND (
        (r.CirculationID IS NOT NULL AND r.FineAmount > 0)
        OR (r.CirculationID IS NULL AND c.DueDate < CURDATE())
    )
    GROUP BY c.CirculationID, m.FinePerDay, r.CirculationID, r.FineAmount
    HAVING CalculatedFine > PaidAmount
";
$stmt = $pdo->prepare($fines_query);
$stmt->execute(["member_no" => $member_no]);
$fines_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pending_fines_total = 0;
foreach ($fines_rows as $fine_row) {
    print_r($fine_row);
    $pending_fines_total += ((float)$fine_row["CalculatedFine"] - (float)$fine_row["PaidAmount"]);
}
echo "Total Pending Fines from dashboard query: $pending_fines_total\n";
if ($pending_fines_total == 0) {
    echo "\nFailure Reason Analysis:\n";
    if (empty($results)) {
        echo "- No circulation records found for MemberNo 2511.\n";
    } else {
        echo "- circulation records exist, but none met the overdue or returned-with-fine criteria.\n";
        echo "- Current Date used in query: " . date("Y-m-d") . "\n";
    }
}
?>
