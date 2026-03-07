<?php
// Stock Verification Page - Content only (no html/body tags)
// This file will be included in the main content area

// Session started by layout.php
require_once 'session_check.php';
require_once '../includes/db_connect.php';

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $current_admin['name'] ?? 'Admin User';
?>

<style>
        .stock-verification-container {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        .stock-verification-container .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #cfac69;
        }

        .stock-verification-container .header h1 {
            color: #263c79;
            font-size: 28px;
        }

        .stock-verification-container .stats-bar {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        @media (max-width: 1200px) {
            .stock-verification-container .stats-bar {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stock-verification-container .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stock-verification-container .stat-box {
            background: linear-gradient(135deg, #263c79 0%, #3d5a9e 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stock-verification-container .stat-box.verified {
            background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
        }

        .stock-verification-container .stat-box.damaged {
            background: linear-gradient(135deg, #dc3545 0%, #e55561 100%);
        }

        .stock-verification-container .stat-box.lost {
            background: linear-gradient(135deg, #ffc107 0%, #ffcd38 100%);
        }

        .stock-verification-container .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stock-verification-container .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .stock-verification-container .scan-section {
            background: #f8f9fa;
            border: 2px dashed #cfac69;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .stock-verification-container .camera-container {
            position: relative;
            max-width: 640px;
            margin: 20px auto;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }

        .stock-verification-container .camera-video {
            width: 100%;
            height: auto;
            display: none;
        }

        .stock-verification-container .scanning-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 280px;
            border: 3px solid #cfac69;
            border-radius: 10px;
            pointer-events: none;
            display: none;
            z-index: 5;
        }

        .stock-verification-container .scanning-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #cfac69, transparent);
            animation: scan 2s linear infinite;
        }

        @keyframes scan {
            0% { top: 0; }
            100% { top: 100%; }
        }

        .stock-verification-container .scanning-overlay.active {
            display: block;
        }

        .stock-verification-container .camera-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 300px;
            background: #263c79;
            color: white;
        }

        .stock-verification-container .camera-placeholder i {
            font-size: 64px;
            margin-bottom: 15px;
        }

        .stock-verification-container .scan-controls {
            margin: 20px 0;
        }

        .stock-verification-container .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin: 0 10px;
        }

        .stock-verification-container .btn-primary {
            background: #263c79;
            color: white;
        }

        .stock-verification-container .btn-primary:hover {
            background: #1a2850;
        }

        .stock-verification-container .btn-success {
            background: #28a745;
            color: white;
        }

        .stock-verification-container .btn-success:hover {
            background: #218838;
        }

        .stock-verification-container .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .stock-verification-container .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .stock-verification-container .manual-entry {
            margin-top: 20px;
        }

        .stock-verification-container .form-control {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            margin: 10px auto;
            display: block;
        }

        .stock-verification-container .book-info-card {
            background: white;
            border: 2px solid #cfac69;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            display: none;
        }

        .stock-verification-container .book-info-card.show {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stock-verification-container .book-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stock-verification-container .detail-item {
            display: flex;
            flex-direction: column;
        }

        .stock-verification-container .detail-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stock-verification-container .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #263c79;
        }

        .stock-verification-container .condition-selector {
            margin: 20px 0;
        }

        .stock-verification-container .condition-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .stock-verification-container .condition-btn {
            padding: 15px 30px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
            font-weight: 600;
        }

        .stock-verification-container .condition-btn:hover {
            border-color: #cfac69;
            transform: translateY(-2px);
        }

        .stock-verification-container .condition-btn.selected {
            background: #263c79;
            color: white;
            border-color: #263c79;
        }

        .stock-verification-container .condition-btn.good.selected {
            background: #28a745;
            border-color: #28a745;
        }

        .stock-verification-container .condition-btn.fair.selected {
            background: #ffc107;
            border-color: #ffc107;
        }

        .stock-verification-container .condition-btn.damaged.selected {
            background: #dc3545;
            border-color: #dc3545;
        }

        .stock-verification-container .condition-btn.lost.selected {
            background: #6c757d;
            border-color: #6c757d;
        }

        .stock-verification-container .remarks-section {
            margin: 20px 0;
        }

        .stock-verification-container .remarks-section textarea {
            width: 100%;
            max-width: 600px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            min-height: 80px;
            resize: vertical;
            font-family: inherit;
        }

        .stock-verification-container .verified-list {
            margin-top: 30px;
        }

        .stock-verification-container .verified-item {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stock-verification-container .verified-item.damaged {
            border-left-color: #dc3545;
        }

        .stock-verification-container .verified-item.fair {
            border-left-color: #ffc107;
        }

        .stock-verification-container .verified-item.lost {
            border-left-color: #6c757d;
        }

        .stock-verification-container .verified-item-info {
            flex: 1;
        }

        .stock-verification-container .verified-item-accno {
            font-weight: 700;
            color: #263c79;
            font-size: 18px;
        }

        .stock-verification-container .verified-item-title {
            color: #666;
            margin-top: 5px;
        }

        .stock-verification-container .verified-item-condition {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
        }

        .stock-verification-container .verified-item-condition.good {
            background: #28a745;
            color: white;
        }

        .stock-verification-container .verified-item-condition.fair {
            background: #ffc107;
            color: #333;
        }

        .stock-verification-container .verified-item-condition.damaged {
            background: #dc3545;
            color: white;
        }

        .stock-verification-container .verified-item-condition.lost {
            background: #6c757d;
            color: white;
        }

        .stock-verification-container .action-buttons {
            margin-top: 30px;
            text-align: center;
        }

        .stock-verification-container .scan-result {
            margin: 10px 0;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
        }

        .stock-verification-container .scan-result.success {
            background: #d4edda;
            color: #155724;
        }

        .stock-verification-container .scan-result.error {
            background: #f8d7da;
            color: #721c24;
        }

        .stock-verification-container .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(38, 60, 121, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            z-index: 10;
        }

        .stock-verification-container .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #cfac69;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin-right: 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
</style>

<div class="stock-verification-container">
        <div class="header">
            <h1><i class="fas fa-clipboard-check"></i> Stock Verification</h1>
            <div>
                <button class="btn btn-secondary" onclick="window.location.hash=''; document.querySelector('[data-page=dashboard]').click();">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </button>
            </div>
        </div>

        <!-- Statistics Bar -->
        <div class="stats-bar">
            <div class="stat-box">
                <div class="stat-number" id="totalScanned">0</div>
                <div class="stat-label">Total Scanned</div>
            </div>
            <div class="stat-box verified">
                <div class="stat-number" id="goodCount">0</div>
                <div class="stat-label">Good Condition</div>
            </div>
            <div class="stat-box lost">
                <div class="stat-number" id="fairCount">0</div>
                <div class="stat-label">Fair Condition</div>
            </div>
            <div class="stat-box damaged">
                <div class="stat-number" id="damagedCount">0</div>
                <div class="stat-label">Damaged</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="lostCount">0</div>
                <div class="stat-label">Lost/Missing</div>
            </div>
        </div>

        <!-- Scan Section -->
        <div class="scan-section">
            <h3><i class="fas fa-qrcode"></i> Scan Book QR Code / Barcode</h3>
            
            <div class="camera-container">
                <div class="loading-overlay" id="cameraLoading">
                    <div class="spinner"></div>
                    <span>Initializing camera...</span>
                </div>
                <div class="scanning-overlay" id="scanningOverlay"></div>
                <video id="bookVideo" class="camera-video" autoplay playsinline></video>
                <div class="camera-placeholder" id="cameraPlaceholder">
                    <div>
                        <i class="fas fa-barcode"></i>
                        <p>Position book barcode or QR code here</p>
                    </div>
                </div>
            </div>

            <div class="scan-controls">
                <button class="btn btn-primary" onclick="startCamera()" id="startBtn">
                    <i class="fas fa-camera"></i> Start Camera
                </button>
                <button class="btn btn-secondary" onclick="stopCamera()" id="stopBtn" disabled>
                    <i class="fas fa-stop"></i> Stop Camera
                </button>
            </div>

            <div class="manual-entry">
                <p style="color: #666; margin-bottom: 10px;">Or enter manually:</p>
                <input type="text" id="accNoInput" class="form-control" placeholder="Enter Accession Number (e.g., ACC001001)" />
                <button class="btn btn-primary" onclick="searchBook()" style="margin-top: 10px;">
                    <i class="fas fa-search"></i> Search Book
                </button>
            </div>

            <div id="scanResult"></div>
        </div>

        <!-- Book Information Card -->
        <div class="book-info-card" id="bookInfoCard">
            <h3><i class="fas fa-book"></i> Book Details</h3>
            <div class="book-details">
                <div class="detail-item">
                    <span class="detail-label">Accession No</span>
                    <span class="detail-value" id="bookAccNo">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Title</span>
                    <span class="detail-value" id="bookTitle">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Author</span>
                    <span class="detail-value" id="bookAuthor">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Current Status</span>
                    <span class="detail-value" id="bookStatus">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Location</span>
                    <span class="detail-value" id="bookLocation">-</span>
                </div>
            </div>

            <div class="condition-selector">
                <h4 style="margin-bottom: 15px; color: #263c79;">Select Condition:</h4>
                <div class="condition-buttons">
                    <button class="condition-btn good" onclick="selectCondition('Good')">
                        <i class="fas fa-check-circle"></i> Good
                    </button>
                    <button class="condition-btn fair" onclick="selectCondition('Fair')">
                        <i class="fas fa-exclamation-circle"></i> Fair
                    </button>
                    <button class="condition-btn damaged" onclick="selectCondition('Damaged')">
                        <i class="fas fa-times-circle"></i> Damaged
                    </button>
                    <button class="condition-btn lost" onclick="selectCondition('Lost')">
                        <i class="fas fa-question-circle"></i> Lost/Missing
                    </button>
                </div>
            </div>

            <div class="remarks-section">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #263c79;">Remarks (Optional):</label>
                <textarea id="remarksInput" placeholder="Enter any observations or notes about the book condition..."></textarea>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button class="btn btn-success" onclick="saveVerification()" id="saveBtn">
                    <i class="fas fa-save"></i> Save & Continue
                </button>
            </div>
        </div>

        <!-- Verified Books List -->
        <div class="verified-list" id="verifiedListSection" style="display: none;">
            <h3><i class="fas fa-list-check"></i> Verified Books (<span id="verifiedCount">0</span>)</h3>
            <div id="verifiedList"></div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-success" onclick="generateReport()" id="reportBtn" style="display: none;">
                <i class="fas fa-file-pdf"></i> Generate Report
            </button>
            <button class="btn btn-secondary" onclick="clearSession()">
                <i class="fas fa-trash"></i> Clear All
            </button>
        </div>
    </div>

    <!-- Include ZXing Library for QR/Barcode Scanning -->
    <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>

    <script>
        let videoStream = null;
        let codeReader = null;
        let currentBook = null;
        let selectedCondition = null;
        let verifiedBooks = [];

        // Statistics
        let stats = {
            total: 0,
            good: 0,
            fair: 0,
            damaged: 0,
            lost: 0
        };

        // Initialize
        initializeCodeReader();
        loadSessionData();
        updateStats();

        function initializeCodeReader() {
            if (typeof ZXing !== 'undefined') {
                codeReader = new ZXing.BrowserMultiFormatReader();
                // Set scan delay to minimal for instant scanning
                codeReader.timeBetweenDecodingAttempts = 100; // Scan every 100ms
                console.log('QR/Barcode reader initialized with fast scanning');
            } else {
                console.warn('ZXing library not loaded yet, retrying in 100ms...');
                setTimeout(initializeCodeReader, 100);
            }
        }

        async function startCamera() {
            try {
                document.getElementById('cameraLoading').style.display = 'flex';
                
                const constraints = {
                    video: { 
                        facingMode: 'environment',
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        focusMode: 'continuous',
                        zoom: 1.0
                    }
                };

                videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = document.getElementById('bookVideo');
                const placeholder = document.getElementById('cameraPlaceholder');
                const startBtn = document.getElementById('startBtn');
                const stopBtn = document.getElementById('stopBtn');

                video.srcObject = videoStream;
                video.style.display = 'block';
                placeholder.style.display = 'none';
                startBtn.disabled = true;
                stopBtn.disabled = false;
                
                // Wait for video to be ready
                await video.play();
                document.getElementById('cameraLoading').style.display = 'none';
                
                // Show scanning overlay
                document.getElementById('scanningOverlay').classList.add('active');

                // Start fast continuous scanning
                if (codeReader) {
                    const hints = new Map();
                    hints.set(ZXing.DecodeHintType.TRY_HARDER, true);
                    hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [
                        ZXing.BarcodeFormat.QR_CODE,
                        ZXing.BarcodeFormat.CODE_128,
                        ZXing.BarcodeFormat.CODE_39,
                        ZXing.BarcodeFormat.EAN_13,
                        ZXing.BarcodeFormat.EAN_8
                    ]);
                    
                    codeReader.decodeFromVideoDevice(null, 'bookVideo', (result, error) => {
                        if (result) {
                            // Play beep sound on successful scan
                            const beep = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUKbh8LdjHAU3kdfy0HotBS17yPLaizsKFFyz6eq' +
                                'mVRQKRp/g8r5sIQUrgc7y2Yk2CBlpu/DknE4MDlCl4fC3YxwFN5HX8tB6LQUte8jy2os7ChRcs+nqplUUCkmf4PK+bCEFK4HO8tmJNggZabvw5JxODA5QpeHwt2McBTeR1/LQei0FLXvI8tqLOwp0XLPQ6aZVFApJn+Dyvmw=');
                            beep.play().catch(() => {});
                            
                            handleScanResult(result.text);
                        }
                    });
                }

            } catch (error) {
                console.error('Error accessing camera:', error);
                document.getElementById('cameraLoading').style.display = 'none';
                showScanResult('Could not access camera. Please check permissions.', 'error');
            }
        }

        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }

            if (codeReader) {
                codeReader.reset();
            }

            const video = document.getElementById('bookVideo');
            const placeholder = document.getElementById('cameraPlaceholder');
            const startBtn = document.getElementById('startBtn');
            const stopBtn = document.getElementById('stopBtn');
            const scanOverlay = document.getElementById('scanningOverlay');

            video.style.display = 'none';
            placeholder.style.display = 'flex';
            scanOverlay.classList.remove('active');
            startBtn.disabled = false;
            stopBtn.disabled = true;
        }

        function handleScanResult(scannedData) {
            console.log('Scanned:', scannedData);

            let accNo = scannedData.trim();

            // Strip QR prefix if present (e.g. 'BOOK:BE8986' -> 'BE8986')
            if (accNo.toUpperCase().startsWith('BOOK:')) { accNo = accNo.substring(5).trim(); }

            // Try to parse JSON if it's structured data
            try {
                const data = JSON.parse(scannedData);
                accNo = data.accNo || data.AccNo || data.barcode || accNo;
            } catch (e) {
                // not JSON
            }

            document.getElementById('accNoInput').value = accNo;
            showScanResult(`Book scanned: ${accNo}`, 'success');
            searchBook();
            stopCamera();
        }

        async function searchBook() {
            let accNo = document.getElementById('accNoInput').value.trim();
            // Strip QR prefix if user typed it manually
            if (accNo.toUpperCase().startsWith('BOOK:')) { accNo = accNo.substring(5).trim(); }
            document.getElementById('accNoInput').value = accNo;

            if (!accNo) {
                showScanResult('Please enter or scan an accession number', 'error');
                return;
            }

            // Check if already verified
            if (verifiedBooks.find(b => b.accNo === accNo)) {
                showScanResult(`Book ${accNo} already verified in this session!`, 'error');
                return;
            }

            try {
                showScanResult('Searching book...', 'success');
                
                const response = await fetch(`api/books.php?action=lookup&accNo=${encodeURIComponent(accNo)}`);
                const result = await response.json();

                if (result.success && result.data) {
                    currentBook = result.data;
                    displayBookInfo(currentBook);
                    showScanResult(`✓ Book found: ${currentBook.Title}`, 'success');
                } else {
                    showScanResult(`Book with AccNo ${accNo} not found in database!`, 'error');
                    currentBook = null;
                }
            } catch (error) {
                console.error('Error searching book:', error);
                showScanResult('Error searching book. Please try again.', 'error');
            }
        }

        function displayBookInfo(book) {
            document.getElementById('bookAccNo').textContent = book.AccNo;
            document.getElementById('bookTitle').textContent = book.Title || 'Unknown';
            document.getElementById('bookAuthor').textContent = book.Author1 || 'N/A';
            document.getElementById('bookStatus').textContent = book.Status || 'Unknown';
            document.getElementById('bookLocation').textContent = book.Location || 'N/A';

            // Reset condition selection
            selectedCondition = null;
            document.querySelectorAll('.condition-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            document.getElementById('remarksInput').value = '';

            document.getElementById('bookInfoCard').classList.add('show');
        }

        function selectCondition(condition) {
            selectedCondition = condition;
            
            // Update button states
            document.querySelectorAll('.condition-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            event.target.closest('.condition-btn').classList.add('selected');
        }

        function saveVerification() {
            if (!currentBook) {
                alert('No book selected!');
                return;
            }

            if (!selectedCondition) {
                alert('Please select a condition!');
                return;
            }

            const remarks = document.getElementById('remarksInput').value.trim();

            // Add to verified list
            const verifiedBook = {
                accNo: currentBook.AccNo,
                title: currentBook.Title,
                author: currentBook.Author1,
                condition: selectedCondition,
                remarks: remarks,
                timestamp: new Date().toISOString()
            };

            verifiedBooks.push(verifiedBook);
            
            // Update statistics
            stats.total++;
            stats[selectedCondition.toLowerCase()]++;
            
            updateStats();
            addToVerifiedList(verifiedBook);
            saveSessionData();

            // Show success message
            showScanResult(`✓ Book ${currentBook.AccNo} verified as ${selectedCondition}`, 'success');

            // Reset form
            currentBook = null;
            selectedCondition = null;
            document.getElementById('bookInfoCard').classList.remove('show');
            document.getElementById('accNoInput').value = '';
            document.getElementById('remarksInput').value = '';

            // Show report button
            document.getElementById('reportBtn').style.display = 'inline-block';

            // Auto-start camera for next scan
            setTimeout(() => {
                startCamera();
            }, 1000);
        }

        function addToVerifiedList(book) {
            const listSection = document.getElementById('verifiedListSection');
            const list = document.getElementById('verifiedList');
            
            listSection.style.display = 'block';
            
            const item = document.createElement('div');
            item.className = `verified-item ${book.condition.toLowerCase()}`;
            item.innerHTML = `
                <div class="verified-item-info">
                    <div class="verified-item-accno">${book.accNo}</div>
                    <div class="verified-item-title">${book.title}</div>
                    <span class="verified-item-condition ${book.condition.toLowerCase()}">${book.condition}</span>
                    ${book.remarks ? `<div style="margin-top: 5px; font-size: 13px; color: #666;"><i class="fas fa-comment"></i> ${book.remarks}</div>` : ''}
                </div>
                <div>
                    <button class="btn btn-secondary" onclick="removeVerified('${book.accNo}')" style="padding: 8px 16px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            list.insertBefore(item, list.firstChild);
            document.getElementById('verifiedCount').textContent = verifiedBooks.length;
        }

        function removeVerified(accNo) {
            if (!confirm('Remove this verification?')) return;
            
            const index = verifiedBooks.findIndex(b => b.accNo === accNo);
            if (index > -1) {
                const book = verifiedBooks[index];
                stats.total--;
                stats[book.condition.toLowerCase()]--;
                
                verifiedBooks.splice(index, 1);
                updateStats();
                refreshVerifiedList();
                saveSessionData();
            }
        }

        function refreshVerifiedList() {
            const list = document.getElementById('verifiedList');
            list.innerHTML = '';
            
            if (verifiedBooks.length === 0) {
                document.getElementById('verifiedListSection').style.display = 'none';
                document.getElementById('reportBtn').style.display = 'none';
            } else {
                verifiedBooks.forEach(book => addToVerifiedList(book));
            }
        }

        function updateStats() {
            document.getElementById('totalScanned').textContent = stats.total;
            document.getElementById('goodCount').textContent = stats.good;
            document.getElementById('fairCount').textContent = stats.fair;
            document.getElementById('damagedCount').textContent = stats.damaged;
            document.getElementById('lostCount').textContent = stats.lost;
        }

        function showScanResult(message, type) {
            const resultDiv = document.getElementById('scanResult');
            resultDiv.className = `scan-result ${type}`;
            resultDiv.textContent = message;
            
            setTimeout(() => {
                resultDiv.textContent = '';
                resultDiv.className = 'scan-result';
            }, 5000);
        }

        function saveSessionData() {
            localStorage.setItem('stockVerification', JSON.stringify({
                verifiedBooks: verifiedBooks,
                stats: stats,
                timestamp: new Date().toISOString()
            }));
        }

        function loadSessionData() {
            const data = localStorage.getItem('stockVerification');
            if (data) {
                const parsed = JSON.parse(data);
                verifiedBooks = parsed.verifiedBooks || [];
                stats = parsed.stats || { total: 0, good: 0, fair: 0, damaged: 0, lost: 0 };
                
                updateStats();
                refreshVerifiedList();
                
                if (verifiedBooks.length > 0) {
                    document.getElementById('reportBtn').style.display = 'inline-block';
                }
            }
        }

        function clearSession() {
            if (!confirm('Clear all verified books? This cannot be undone!')) return;
            
            verifiedBooks = [];
            stats = { total: 0, good: 0, fair: 0, damaged: 0, lost: 0 };
            localStorage.removeItem('stockVerification');
            
            updateStats();
            refreshVerifiedList();
            
            showScanResult('Session cleared', 'success');
        }

        async function generateReport() {
            if (verifiedBooks.length === 0) {
                alert('No books verified yet!');
                return;
            }

            // Generate PDF-style report
            const reportWindow = window.open('', '_blank');
            
            const reportHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Stock Verification Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 40px; }
                        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #263c79; padding-bottom: 20px; }
                        .header h1 { color: #263c79; margin: 0; }
                        .header p { color: #666; margin: 5px 0; }
                        .summary { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
                        .summary-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; text-align: center; }
                        .summary-item { padding: 15px; background: white; border-radius: 6px; }
                        .summary-number { font-size: 32px; font-weight: bold; color: #263c79; }
                        .summary-label { font-size: 14px; color: #666; margin-top: 5px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th { background: #263c79; color: white; padding: 12px; text-align: left; }
                        td { border: 1px solid #ddd; padding: 10px; }
                        tr:nth-child(even) { background: #f8f9fa; }
                        .condition { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
                        .condition.good { background: #28a745; color: white; }
                        .condition.fair { background: #ffc107; color: #333; }
                        .condition.damaged { background: #dc3545; color: white; }
                        .condition.lost { background: #6c757d; color: white; }
                        @media print {
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>📚 Stock Verification Report</h1>
                        <p>Generated on: ${new Date().toLocaleString('en-IN')}</p>
                        <p>Verified by: <?php echo $admin_name; ?></p>
                    </div>

                    <div class="summary">
                        <h2>Summary</h2>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <div class="summary-number">${stats.total}</div>
                                <div class="summary-label">Total Books</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number">${stats.good}</div>
                                <div class="summary-label">Good</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number">${stats.fair}</div>
                                <div class="summary-label">Fair</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number">${stats.damaged}</div>
                                <div class="summary-label">Damaged</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number">${stats.lost}</div>
                                <div class="summary-label">Lost</div>
                            </div>
                        </div>
                    </div>

                    <h2>Verified Books</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Accession No</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Condition</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${verifiedBooks.map((book, index) => `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td><strong>${book.accNo}</strong></td>
                                    <td>${book.title}</td>
                                    <td>${book.author || 'N/A'}</td>
                                    <td><span class="condition ${book.condition.toLowerCase()}">${book.condition}</span></td>
                                    <td>${book.remarks || '-'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                    <div style="margin-top: 40px; text-align: center;" class="no-print">
                        <button onclick="window.print()" style="padding: 12px 30px; background: #263c79; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer;">
                            🖨️ Print Report
                        </button>
                        <button onclick="window.close()" style="padding: 12px 30px; background: #6c757d; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-left: 10px;">
                            Close
                        </button>
                    </div>
                </body>
                </html>
            `;

            reportWindow.document.write(reportHTML);
            reportWindow.document.close();
        }

        // Make functions globally accessible
        window.startCamera = startCamera;
        window.stopCamera = stopCamera;
        window.searchBook = searchBook;
        window.selectCondition = selectCondition;
        window.saveVerification = saveVerification;
        window.removeVerified = removeVerified;
        window.clearSession = clearSession;
        window.generateReport = generateReport;
    </script>
</div>
