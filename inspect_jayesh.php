<?php
require_once "includes/db_connect.php";
function run_q($pdo, $sql) { try { $stmt = $pdo->query($sql); return $stmt->fetchAll(); } catch(Exception $e) { echo $e->getMessage(); return []; } }
$name = "%Jayesh%";
echo "1) STUDENT TABLE\n";
$s = run_q($pdo, "SELECT StudentID, MemberNo, Email, FirstName, LastName FROM Student WHERE FirstName LIKE \"$name\" OR LastName LIKE \"%Adurkar%\"");
print_r($s);
if($s) { $mno = $s[0]["MemberNo"];
echo "\n2) MEMBER TABLE\n";
print_r(run_q($pdo, "SELECT MemberNo, Name FROM Member WHERE MemberNo = \"$mno\" OR Name LIKE \"$name\""));
echo "\n3) CIRCULATION\n";
$c = run_q($pdo, "SELECT CirculationID, MemberNo, DueDate, AccNo FROM Circulation WHERE MemberNo = \"$mno\"");
print_r($c);
if($c) { echo "\n4) RETURNS/FINES\n"; $ids = implode(",", array_column($c, "CirculationID"));
print_r(run_q($pdo, "SELECT * FROM `Return` WHERE CirculationID IN ($ids)"));
print_r(run_q($pdo, "SELECT * FROM FinePayment WHERE CirculationID IN ($ids)")); } }
?>
