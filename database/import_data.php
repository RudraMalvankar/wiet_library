<?php
/**
 * Data Import Script
 * Imports book and member data from data.md into the database
 */

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

echo "<h2>WIET Library - Data Import Script</h2>";
echo "<hr>";

// Import Books from data.md
echo "<h3>Importing Books...</h3>";

$booksData = [
    [
        'AccNo' => 'BE8950',
        'AccDate' => '2025-07-14',
        'CatNo' => 10084,
        'Author1' => 'LUCAS, H.C.',
        'Title' => 'INFORMATION TECHNOLOGY FOR MANAGEMENT',
        'Edition' => '7th Ed.',
        'Year' => 2001,
        'Place' => 'NEW DELHI',
        'Publisher' => 'TATA McGRAW HILL',
        'Pages' => '730p.',
        'ClassNo' => '1.642',
        'BookNo' => 'LUC',
        'Collection' => 'C',
        'Location' => 'CMTC',
        'Section' => 'T',
        'Subject' => 'Information Technology',
        'Language' => 'English',
        'DocumentType' => 'BK'
    ],
    [
        'AccNo' => 'BE8951',
        'AccDate' => '2025-07-14',
        'CatNo' => 10085,
        'Author1' => 'SILBERSCHATZ, A.',
        'Author2' => 'KORTH, H.F.',
        'Title' => 'DATABASE SYSTEM CONCEPTS',
        'Edition' => '6th Ed.',
        'Year' => 2010,
        'Place' => 'NEW YORK',
        'Publisher' => 'McGRAW HILL',
        'Pages' => '1376p.',
        'ClassNo' => '1.643',
        'BookNo' => 'SIL',
        'Collection' => 'C',
        'Location' => 'CMTC',
        'Section' => 'T',
        'Subject' => 'Database Systems',
        'Language' => 'English',
        'DocumentType' => 'BK',
        'ISBN' => '978-0073523323'
    ],
    [
        'AccNo' => 'BE8952',
        'AccDate' => '2025-07-15',
        'CatNo' => 10086,
        'Author1' => 'TANENBAUM, A.S.',
        'Title' => 'COMPUTER NETWORKS',
        'Edition' => '5th Ed.',
        'Year' => 2010,
        'Place' => 'DELHI',
        'Publisher' => 'PEARSON',
        'Pages' => '960p.',
        'ClassNo' => '1.644',
        'BookNo' => 'TAN',
        'Collection' => 'C',
        'Location' => 'CMTC',
        'Section' => 'T',
        'Subject' => 'Computer Networks',
        'Language' => 'English',
        'DocumentType' => 'BK',
        'ISBN' => '978-0132126953'
    ]
];


$importedBooks = 0;
$importedHoldings = 0;

// Helper: Generate barcode/QR file path (simulate)
function generateBarcodePath($accNo) {
    return 'barcodes/' . $accNo . '.png';
}

try {
    foreach ($booksData as $bookData) {
        // Check if book already exists
        $stmt = $pdo->prepare("SELECT CatNo FROM Books WHERE CatNo = ?");
        $stmt->execute([$bookData['CatNo']]);
        if (!$stmt->fetch()) {
            // Insert book (all fields from data.md)
            $stmt = $pdo->prepare("
                INSERT INTO Books (CatNo, Title, Author1, Author2, Author3, Edition, Year, Place, 
                                  Publisher, Pages, ClassNo, BookNo, Collection, Subject, Language, DocumentType, ISBN)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $bookData['CatNo'],
                $bookData['Title'],
                $bookData['Author1'],
                $bookData['Author2'] ?? null,
                $bookData['Author3'] ?? null,
                $bookData['Edition'] ?? null,
                $bookData['Year'] ?? null,
                $bookData['Place'] ?? null,
                $bookData['Publisher'] ?? null,
                $bookData['Pages'] ?? null,
                $bookData['ClassNo'] ?? null,
                $bookData['BookNo'] ?? null,
                $bookData['Collection'] ?? null,
                $bookData['Subject'] ?? null,
                $bookData['Language'] ?? 'English',
                $bookData['DocumentType'] ?? 'BK',
                $bookData['ISBN'] ?? null
            ]);
            $importedBooks++;
            echo "✓ Added book: {$bookData['Title']}<br>";
        }
        // Check if holding already exists
        $stmt = $pdo->prepare("SELECT AccNo FROM Holding WHERE AccNo = ?");
        $stmt->execute([$bookData['AccNo']]);
        if (!$stmt->fetch()) {
            // Insert holding (all fields, plus barcode/QR path)
            $barcodePath = generateBarcodePath($bookData['AccNo']);
            $stmt = $pdo->prepare("
                INSERT INTO Holding (AccNo, CatNo, AccDate, ClassNo, BookNo, Status, 
                                    Location, Section, Collection, BarCode)
                VALUES (?, ?, ?, ?, ?, 'Available', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $bookData['AccNo'],
                $bookData['CatNo'],
                $bookData['AccDate'] ?? date('Y-m-d'),
                $bookData['ClassNo'] ?? null,
                $bookData['BookNo'] ?? null,
                $bookData['Location'] ?? null,
                $bookData['Section'] ?? null,
                $bookData['Collection'] ?? null,
                $barcodePath
            ]);
            $importedHoldings++;
            echo "✓ Added holding: {$bookData['AccNo']} (Barcode: $barcodePath)<br>";
        }
    }
    
    echo "<p style='color: green;'><strong>Books Import Complete!</strong> Imported {$importedBooks} books and {$importedHoldings} holdings.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error importing books: " . $e->getMessage() . "</p>";
}

// Import Members from data.md
echo "<hr><h3>Importing Members...</h3>";

$membersData = [
    [
        'MemberNo' => 2511,
        'MemberName' => 'Jayesh Mahesh Adurkar',
        'Group' => 'Student',
        'Designation' => 'FE',
        'Phone' => '9146622724',
        'Email' => 'manishaadurkar44@gmail.com',
        'AdmissionDate' => '2025-09-15',
        'ClosingDate' => '2029-05-31',
        'Surname' => 'Adurkar',
        'MiddleName' => 'Mahesh',
        'FirstName' => 'Jayesh',
        'Gender' => 'Male',
        'Branch' => 'Computer',
        'CourseName' => 'Computer Engineering',
        'PRN' => 'C2511',
        'Address' => 'Room no 2743 lahu jamdare chawl behind bethal church gautum nagar ambernath west',
        'CardColour' => 'Green'
    ],
    [
        'MemberNo' => 2512,
        'MemberName' => 'Rahul Kumar Sharma',
        'Group' => 'Student',
        'Designation' => 'SE',
        'Phone' => '9876543210',
        'Email' => 'rahul.sharma@student.wiet.edu.in',
        'AdmissionDate' => '2024-08-01',
        'ClosingDate' => '2028-05-31',
        'Surname' => 'Sharma',
        'MiddleName' => 'Kumar',
        'FirstName' => 'Rahul',
        'Gender' => 'Male',
        'Branch' => 'IT',
        'CourseName' => 'Information Technology',
        'PRN' => 'C2512',
        'Address' => 'Flat 101, Sai Residency, Ambernath East',
        'CardColour' => 'Blue'
    ],
    [
        'MemberNo' => 3001,
        'MemberName' => 'Dr. Priya Mehta',
        'Group' => 'Faculty',
        'Designation' => 'Associate Professor',
        'Phone' => '9988776655',
        'Email' => 'priya.mehta@wiet.edu.in',
        'AdmissionDate' => '2020-07-01',
        'EmployeeID' => 'EMP3001',
        'Department' => 'Computer Engineering'
    ]
];

$importedMembers = 0;
$importedStudents = 0;
$importedFaculty = 0;

try {
    foreach ($membersData as $memberData) {
        // Check if member already exists
        $stmt = $pdo->prepare("SELECT MemberNo FROM Member WHERE MemberNo = ?");
        $stmt->execute([$memberData['MemberNo']]);
        if (!$stmt->fetch()) {
            // Insert member (all fields from data.md)
            $stmt = $pdo->prepare("
                INSERT INTO Member (MemberNo, MemberName, `Group`, Designation, Phone, 
                                   Email, AdmissionDate, ClosingDate, Status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')
            ");
            $stmt->execute([
                $memberData['MemberNo'],
                $memberData['MemberName'],
                $memberData['Group'],
                $memberData['Designation'] ?? null,
                $memberData['Phone'] ?? null,
                $memberData['Email'] ?? null,
                $memberData['AdmissionDate'] ?? date('Y-m-d'),
                $memberData['ClosingDate'] ?? null
            ]);
            $importedMembers++;
            echo "✓ Added member: {$memberData['MemberName']}<br>";
            // If student, add student details and login
            if ($memberData['Group'] === 'Student' && isset($memberData['PRN'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO Student (MemberNo, Surname, MiddleName, FirstName, Gender, 
                                        Branch, CourseName, PRN, Mobile, Email, Address, CardColour)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $memberData['MemberNo'],
                    $memberData['Surname'] ?? null,
                    $memberData['MiddleName'] ?? null,
                    $memberData['FirstName'] ?? null,
                    $memberData['Gender'] ?? null,
                    $memberData['Branch'] ?? null,
                    $memberData['CourseName'] ?? null,
                    $memberData['PRN'],
                    $memberData['Phone'],
                    $memberData['Email'],
                    $memberData['Address'] ?? null,
                    $memberData['CardColour'] ?? 'Blue'
                ]);
                $importedStudents++;
                echo "  ✓ Added student details for PRN: {$memberData['PRN']}<br>";
                // Insert login credentials for student (email/12345)
                if (!empty($memberData['Email'])) {
                    $hashedPassword = password_hash('12345', PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO UserLogin (Username, PasswordHash, MemberNo, Role) VALUES (?, ?, ?, 'student')");
                    $stmt->execute([
                        $memberData['Email'],
                        $hashedPassword,
                        $memberData['MemberNo']
                    ]);
                    echo "  ✓ Created login for student: {$memberData['Email']} / 12345<br>";
                }
            }
            // If faculty, add faculty details
            if ($memberData['Group'] === 'Faculty' && isset($memberData['EmployeeID'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO Faculty (MemberNo, EmployeeID, Department, Designation, 
                                        JoinDate, Mobile, Email)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $memberData['MemberNo'],
                    $memberData['EmployeeID'],
                    $memberData['Department'] ?? null,
                    $memberData['Designation'] ?? null,
                    $memberData['AdmissionDate'],
                    $memberData['Phone'],
                    $memberData['Email']
                ]);
                $importedFaculty++;
                echo "  ✓ Added faculty details for Employee: {$memberData['EmployeeID']}<br>";
            }
        }
    }
    
    echo "<p style='color: green;'><strong>Members Import Complete!</strong> Imported {$importedMembers} members ({$importedStudents} students, {$importedFaculty} faculty).</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error importing members: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<ul>";
echo "<li>Books imported: {$importedBooks}</li>";
echo "<li>Holdings imported: {$importedHoldings}</li>";
echo "<li>Members imported: {$importedMembers}</li>";
echo "<li>Students imported: {$importedStudents}</li>";
echo "<li>Faculty imported: {$importedFaculty}</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='../admin/dashboard.php'>Go to Admin Dashboard</a></p>";
?>
