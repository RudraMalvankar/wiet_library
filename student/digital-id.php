<?php
// Digital ID Content - Student digital library card with QR and barcode
// This file will be included in the main content area

// Session management
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: student_login.php');
    exit();
}

// Include database connection
require_once '../includes/db_connect.php';

// Get student information from session
$student_id = $_SESSION['student_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;

// Fetch real student data from database
$digital_card = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            s.StudentID,
            s.MemberNo,
            s.FirstName,
            s.MiddleName,
            s.Surname,
            s.Email,
            s.Mobile,
            s.Branch,
            s.CourseName,
            s.PRN,
            s.ValidTill,
            s.QRCode,
            s.CardColour,
            s.Photo,
            m.MemberName,
            m.AdmissionDate,
            m.Status,
            m.BooksIssued,
            m.Entitlement
        FROM Student s
        INNER JOIN Member m ON s.MemberNo = m.MemberNo
        WHERE s.StudentID = ?
        LIMIT 1
    ");
    $stmt->execute([$student_id]);
    $student_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student_data) {
        $digital_card = [
            'member_no' => 'M' . str_pad($student_data['MemberNo'], 7, '0', STR_PAD_LEFT),
            'student_id' => $student_data['PRN'] ?? $student_data['StudentID'],
            'name' => $student_data['MemberName'],
            'course' => $student_data['CourseName'] ?? 'N/A',
            'year' => '', // Can be calculated from AdmissionDate
            'department' => $student_data['Branch'] ?? 'N/A',
            'issue_date' => $student_data['AdmissionDate'] ?? date('Y-m-d'),
            'expiry_date' => $student_data['ValidTill'] ?? 'N/A',
            'status' => $student_data['Status'],
            'barcode' => str_pad($student_data['MemberNo'], 12, '0', STR_PAD_LEFT),
            'qr_code' => $student_data['QRCode'] ?? ($student_data['PRN'] . '_' . date('Y')),
            'email' => $student_data['Email'],
            'mobile' => $student_data['Mobile'],
            'books_issued' => $student_data['BooksIssued'],
            'entitlement' => $student_data['Entitlement'] ?? 'Standard',
            'photo' => $student_data['Photo']
        ];
    }
} catch (PDOException $e) {
    error_log("Digital ID fetch error: " . $e->getMessage());
    $digital_card = [
        'member_no' => 'M' . str_pad($member_no, 7, '0', STR_PAD_LEFT),
        'student_id' => $_SESSION['student_prn'] ?? $student_id,
        'name' => $_SESSION['student_name'] ?? 'Student',
        'course' => $_SESSION['student_course'] ?? 'N/A',
        'year' => '',
        'department' => $_SESSION['student_branch'] ?? 'N/A',
        'issue_date' => date('Y-m-d'),
        'expiry_date' => 'N/A',
        'status' => 'Active',
        'barcode' => str_pad($member_no, 12, '0', STR_PAD_LEFT),
        'qr_code' => $student_id . '_' . date('Y')
    ];
}

$card_features = [
    'Library Access' => 'Physical and digital library access',
    'Book Borrowing' => 'Borrow up to 5 books for 21 days',
    'Digital Resources' => 'Access to online databases and e-books',
    'Renewal Rights' => 'Renew books up to 2 times',
    'Study Areas' => 'Reserved seating in study halls',
    'Printing Services' => '100 free pages per month'
];
?>

<style>
    /* Digital ID specific styles */
    .digital-id-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .digital-id-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 5px 0;
    }

    .digital-id-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .card-container {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 30px;
        margin-bottom: 30px;
    }

    .digital-card {
        background: linear-gradient(135deg, #263c79 0%, #1e2f5a 100%);
        border-radius: 15px;
        padding: 30px;
        color: white;
        box-shadow: 0 8px 25px rgba(38, 60, 121, 0.3);
        position: relative;
        overflow: hidden;
    }

    .digital-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(207,172,105,0.1)" stroke-width="1"/></svg>');
        opacity: 0.3;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 25px;
        position: relative;
        z-index: 2;
    }

    .college-info {
        flex: 1;
    }

    .college-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #cfac69;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-type {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.8);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge {
        background: #28a745;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .student-info {
        position: relative;
        z-index: 2;
        margin-bottom: 25px;
    }

    .student-name {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
        color: white;
    }

    .student-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        font-size: 13px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        color: #cfac69;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }

    .detail-value {
        color: white;
        font-weight: 500;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 2;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(207, 172, 105, 0.3);
    }

    .validity-info {
        font-size: 12px;
    }

    .validity-label {
        color: #cfac69;
        margin-bottom: 2px;
    }

    .validity-date {
        color: white;
        font-weight: 600;
    }

    .member-no {
        font-family: monospace;
        font-size: 14px;
        font-weight: 600;
        color: #cfac69;
    }

    .codes-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .codes-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .qr-code,
    .barcode {
        background: #f8f9fa;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 120px;
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }

    .qr-code {
        margin-bottom: 20px;
        background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%),
            linear-gradient(-45deg, #f8f9fa 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #f8f9fa 75%),
            linear-gradient(-45deg, transparent 75%, #f8f9fa 75%);
        background-size: 8px 8px;
        background-position: 0 0, 0 4px, 4px -4px, -4px 0px;
    }

    .code-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .code-value {
        font-family: monospace;
        font-weight: 600;
        color: #263c79;
        font-size: 13px;
    }

    .download-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }

    .download-btn {
        background: #263c79;
        color: white;
        border: none;
        padding: 12px 15px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .download-btn:hover {
        background: #1e2f5a;
        color: white;
        text-decoration: none;
    }

    .download-btn.secondary {
        background: transparent;
        color: #263c79;
        border: 2px solid #263c79;
    }

    .download-btn.secondary:hover {
        background: #263c79;
        color: white;
    }

    .features-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .features-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #e0e0e0;
    }

    .features-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .features-content {
        padding: 25px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .feature-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #cfac69;
    }

    .feature-icon {
        color: #263c79;
        font-size: 18px;
        margin-top: 2px;
    }

    .feature-content {
        flex: 1;
    }

    .feature-name {
        font-weight: 600;
        color: #263c79;
        margin-bottom: 3px;
        font-size: 14px;
    }

    .feature-description {
        color: #666;
        font-size: 13px;
        line-height: 1.4;
    }

    .usage-instructions {
        background: #e3f2fd;
        border: 1px solid #90caf9;
        border-radius: 8px;
        padding: 20px;
        margin-top: 30px;
    }

    .instructions-title {
        color: #1565c0;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .instructions-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .instruction-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
        color: #1565c0;
        font-size: 14px;
    }

    .instruction-number {
        background: #1565c0;
        color: white;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
        flex-shrink: 0;
        margin-top: 1px;
    }

    @media (max-width: 768px) {
        .card-container {
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .digital-card {
            padding: 25px;
        }

        .student-details {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .card-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .features-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }
</style>

<div class="digital-id-header">
    <h1 class="digital-id-title">Digital ID Card</h1>
    <p class="digital-id-subtitle">Your official WIET library membership card</p>
</div>

<div class="card-container">
    <!-- Digital Card -->
    <div class="digital-card">
        <div class="card-header">
            <div class="college-info">
                <div class="college-name">WIET College Library</div>
                <div class="card-type">Student Membership Card</div>
            </div>
            <div class="status-badge"><?php echo $digital_card['status']; ?></div>
        </div>

        <div class="student-info">
            <h2 class="student-name"><?php echo htmlspecialchars($digital_card['name']); ?></h2>
            <div class="student-details">
                <div class="detail-item">
                    <div class="detail-label">Student ID</div>
                    <div class="detail-value"><?php echo htmlspecialchars($digital_card['student_id']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Course</div>
                    <div class="detail-value"><?php echo htmlspecialchars($digital_card['course']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Year</div>
                    <div class="detail-value"><?php echo htmlspecialchars($digital_card['year']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Department</div>
                    <div class="detail-value"><?php echo htmlspecialchars($digital_card['department']); ?></div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="validity-info">
                <div class="validity-label">Valid Until</div>
                <div class="validity-date"><?php echo date('M j, Y', strtotime($digital_card['expiry_date'])); ?></div>
            </div>
            <div class="member-no">
                Member: <?php echo htmlspecialchars($digital_card['member_no']); ?>
            </div>
        </div>
    </div>

    <!-- QR & Barcode Section -->
    <div class="codes-section">
        <h3 class="codes-title">Scan Codes</h3>

        <div class="qr-code">
            <div>
                <div class="code-label">QR Code</div>
                <div id="qrcode" style="margin: 10px 0;"></div>
                <div class="code-value"><?php echo htmlspecialchars($digital_card['qr_code']); ?></div>
            </div>
        </div>

        <div class="barcode">
            <div>
                <div class="code-label">Barcode</div>
                <svg id="barcode" style="margin: 10px 0;"></svg>
                <div class="code-value"><?php echo htmlspecialchars($digital_card['barcode']); ?></div>
            </div>
        </div>

        <div class="download-actions">
            <button class="download-btn" onclick="downloadCard()">
                <i class="fas fa-download"></i>
                Download Card (PNG)
            </button>
            <button class="download-btn secondary" onclick="printCard()">
                <i class="fas fa-print"></i>
                Print Card
            </button>
        </div>
    </div>
</div>

<!-- Card Features -->
<div class="features-section">
    <div class="features-header">
        <h3 class="features-title">
            <i class="fas fa-star"></i>
            Membership Benefits
        </h3>
    </div>
    <div class="features-content">
        <div class="features-grid">
            <?php foreach ($card_features as $feature => $description): ?>
                <div class="feature-item">
                    <i class="feature-icon fas fa-check-circle"></i>
                    <div class="feature-content">
                        <div class="feature-name"><?php echo htmlspecialchars($feature); ?></div>
                        <div class="feature-description"><?php echo htmlspecialchars($description); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Usage Instructions -->
<div class="usage-instructions">
    <h3 class="instructions-title">
        <i class="fas fa-info-circle"></i>
        How to Use Your Digital ID
    </h3>
    <ul class="instructions-list">
        <li class="instruction-item">
            <span class="instruction-number">1</span>
            Show this digital card or scan QR code at library entrance for access
        </li>
        <li class="instruction-item">
            <span class="instruction-number">2</span>
            Present card to librarian when borrowing or returning books
        </li>
        <li class="instruction-item">
            <span class="instruction-number">3</span>
            Use barcode for quick scanning at self-service kiosks
        </li>
        <li class="instruction-item">
            <span class="instruction-number">4</span>
            Download or print card for offline access when needed
        </li>
    </ul>
</div>

<script>
    // QR Code generation using QRCode.js
    function generateQRCode() {
        const qrContainer = document.getElementById('qrcode');
        if (!qrContainer) return;
        
        // Clear existing QR code
        qrContainer.innerHTML = '';
        
        // Generate QR code
        new QRCode(qrContainer, {
            text: '<?php echo htmlspecialchars($digital_card['qr_code']); ?>',
            width: 150,
            height: 150,
            colorDark: '#263c79',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    // Barcode generation using JsBarcode
    function generateBarcode() {
        const barcodeElement = document.getElementById('barcode');
        if (!barcodeElement) return;
        
        try {
            JsBarcode(barcodeElement, '<?php echo htmlspecialchars($digital_card['barcode']); ?>', {
                format: 'CODE128',
                width: 2,
                height: 60,
                displayValue: false,
                background: '#ffffff',
                lineColor: '#263c79'
            });
        } catch (error) {
            console.error('Barcode generation error:', error);
            barcodeElement.innerHTML = '<text y="30" x="50%" text-anchor="middle">Barcode Error</text>';
        }
    }

    // Download card as PNG
    async function downloadCard() {
        try {
            // Hide download buttons temporarily
            const downloadActions = document.querySelector('.download-actions');
            const originalDisplay = downloadActions.style.display;
            downloadActions.style.display = 'none';
            
            // Get the card container
            const cardContainer = document.querySelector('.digital-card-container');
            
            // Use html2canvas to capture the card
            const canvas = await html2canvas(cardContainer, {
                backgroundColor: '#f5f6fa',
                scale: 2, // Higher quality
                logging: false,
                useCORS: true
            });
            
            // Restore download buttons
            downloadActions.style.display = originalDisplay;
            
            // Convert canvas to blob and download
            canvas.toBlob(function(blob) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'WIET_Library_Digital_ID_<?php echo $digital_card['member_no']; ?>.png';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });
            
        } catch (error) {
            console.error('Download error:', error);
            alert('Failed to download card. Please try printing instead.');
        }
    }

    // Print card
    function printCard() {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        const cardHTML = document.querySelector('.digital-card-container').outerHTML;
        const featuresHTML = document.querySelector('.features-section').outerHTML;
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>WIET Library Digital ID - <?php echo $digital_card['member_no']; ?></title>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    <?php
                    // Include the same styles
                    $style_start = strpos(file_get_contents(__FILE__), '<style>');
                    $style_end = strpos(file_get_contents(__FILE__), '</style>');
                    if ($style_start !== false && $style_end !== false) {
                        echo substr(file_get_contents(__FILE__), $style_start + 7, $style_end - $style_start - 7);
                    }
                    ?>
                    body {
                        padding: 20px;
                    }
                    .download-actions {
                        display: none !important;
                    }
                    @media print {
                        body {
                            padding: 0;
                        }
                        .digital-card-container {
                            page-break-inside: avoid;
                        }
                        .features-section {
                            page-break-before: always;
                        }
                    }
                </style>
            </head>
            <body>
                ${cardHTML}
                ${featuresHTML}
                <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"><\/script>
                <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
                <script>
                    // Generate QR code
                    new QRCode(document.getElementById('qrcode'), {
                        text: '<?php echo htmlspecialchars($digital_card['qr_code']); ?>',
                        width: 150,
                        height: 150,
                        colorDark: '#263c79',
                        colorLight: '#ffffff'
                    });
                    
                    // Generate barcode
                    JsBarcode('#barcode', '<?php echo htmlspecialchars($digital_card['barcode']); ?>', {
                        format: 'CODE128',
                        width: 2,
                        height: 60,
                        displayValue: false,
                        background: '#ffffff',
                        lineColor: '#263c79'
                    });
                    
                    // Auto print after codes are generated
                    setTimeout(function() {
                        window.print();
                        // Close window after printing (optional)
                        // window.onafterprint = function() { window.close(); };
                    }, 500);
                <\/script>
            </body>
            </html>
        `);
        
        printWindow.document.close();
    }

    // Initialize codes when page loads
    document.addEventListener('DOMContentLoaded', function() {
        generateQRCode();
        generateBarcode();
    });
</script>

<!-- External Libraries for QR Code and Barcode -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

