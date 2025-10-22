<?php
// Digital ID Content - Student digital library card with QR and barcode
// This file will be included in the main content area

// Session management
session_start();

// Mock data for demonstration - replace with actual database queries
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : "John Doe";
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : "STU2024001";

$digital_card = [
    'member_no' => 'M2024001',
    'student_id' => $student_id,
    'name' => $student_name,
    'course' => 'B.Tech Computer Science',
    'year' => '3rd Year',
    'department' => 'Computer Science & Engineering',
    'issue_date' => '2024-08-15',
    'expiry_date' => '2025-08-14',
    'status' => 'Active',
    'barcode' => '123456789012',
    'qr_code' => $student_id . '_' . date('Y')
];

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
                <div style="font-size: 48px; margin: 10px 0;">⬜</div>
                <div class="code-value"><?php echo htmlspecialchars($digital_card['qr_code']); ?></div>
            </div>
        </div>

        <div class="barcode">
            <div>
                <div class="code-label">Barcode</div>
                <div style="font-family: monospace; font-size: 20px; margin: 10px 0; letter-spacing: 2px;">||||| |||| |||||</div>
                <div class="code-value"><?php echo htmlspecialchars($digital_card['barcode']); ?></div>
            </div>
        </div>

        <div class="download-actions">
            <a href="#" class="download-btn" onclick="downloadCard()">
                <i class="fas fa-download"></i>
                Download Card
            </a>
            <a href="#" class="download-btn secondary" onclick="printCard()">
                <i class="fas fa-print"></i>
                Print Card
            </a>
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
    function downloadCard() {
        // Simulate download functionality
        alert('Digital ID card download will be available soon!');
        // In real implementation, this would generate and download a PDF/image
    }

    function printCard() {
        // Open print dialog for the card section
        window.print();
    }
</script>