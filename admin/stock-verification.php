<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Verification - Library Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #cfac69;
        }

        .header h1 {
            color: #263c79;
            font-size: 28px;
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: linear-gradient(135deg, #263c79 0%, #3d5a9e 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-box.verified {
            background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
        }

        .stat-box.damaged {
            background: linear-gradient(135deg, #dc3545 0%, #e55561 100%);
        }

        .stat-box.lost {
            background: linear-gradient(135deg, #ffc107 0%, #ffcd38 100%);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .scan-section {
            background: #f8f9fa;
            border: 2px dashed #cfac69;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .camera-container {
            position: relative;
            max-width: 640px;
            margin: 20px auto;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }

        .camera-video {
            width: 100%;
            height: auto;
            display: none;
        }

        .camera-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 300px;
            background: #263c79;
            color: white;
        }

        .camera-placeholder i {
            font-size: 64px;
            margin-bottom: 15px;
        }

        .scan-controls {
            margin: 20px 0;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin: 0 10px;
        }

        .btn-primary {
            background: #263c79;
            color: white;
        }

        .btn-primary:hover {
            background: #1a2850;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .manual-entry {
            margin-top: 20px;
        }

        .form-control {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            margin: 10px auto;
            display: block;
        }

        .book-info-card {
            background: white;
            border: 2px solid #cfac69;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            display: none;
        }

        .book-info-card.show {
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

        .book-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #263c79;
        }

        .condition-selector {
            margin: 20px 0;
        }

        .condition-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .condition-btn {
            padding: 15px 30px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
            font-weight: 600;
        }

        .condition-btn:hover {
            border-color: #cfac69;
            transform: translateY(-2px);
        }

        .condition-btn.selected {
            background: #263c79;
            color: white;
            border-color: #263c79;
        }

        .condition-btn.good.selected {
            background: #28a745;
            border-color: #28a745;
        }

        .condition-btn.fair.selected {
            background: #ffc107;
            border-color: #ffc107;
        }

        .condition-btn.damaged.selected {
            background: #dc3545;
            border-color: #dc3545;
        }

        .condition-btn.lost.selected {
            background: #6c757d;
            border-color: #6c757d;
        }

        .remarks-section {
            margin: 20px 0;
        }

        .remarks-section textarea {
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

        .verified-list {
            margin-top: 30px;
        }

        .verified-item {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .verified-item.damaged {
            border-left-color: #dc3545;
        }

        .verified-item.fair {
            border-left-color: #ffc107;
        }

        .verified-item.lost {
            border-left-color: #6c757d;
        }

        .verified-item-info {
            flex: 1;
        }

        .verified-item-accno {
            font-weight: 700;
            color: #263c79;
            font-size: 18px;
        }

        .verified-item-title {
            color: #666;
            margin-top: 5px;
        }

        .verified-item-condition {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
        }

        .verified-item-condition.good {
            background: #28a745;
            color: white;
        }

        .verified-item-condition.fair {
            background: #ffc107;
            color: #333;
        }

        .verified-item-condition.damaged {
            background: #dc3545;
            color: white;
        }

        .verified-item-condition.lost {
            background: #6c757d;
            color: white;
        }

        .action-buttons {
            margin-top: 30px;
            text-align: center;
        }

        .scan-result {
            margin: 10px 0;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
        }

        .scan-result.success {
            background: #d4edda;
            color: #155724;
        }

        .scan-result.error {
            background: #f8d7da;
            color: #721c24;
        }

        .loading-overlay {
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

        .spinner {
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
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clipboard-check"></i> Stock Verification</h1>
            <div>
                <button class="btn btn-secondary" onclick="window.location.href='dashboard.php'">
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
        document.addEventListener('DOMContentLoaded', function() {
            initializeCodeReader();
            loadSessionData();
            updateStats();
        });

        function initializeCodeReader() {
            if (typeof ZXing !== 'undefined') {
                codeReader = new ZXing.BrowserMultiFormatReader();
                console.log('QR/Barcode reader initialized');
            } else {
                console.error('ZXing library not loaded');
            }
        }

        async function startCamera() {
            try {
                document.getElementById('cameraLoading').style.display = 'flex';
                
                const constraints = {
                    video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 480 } }
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
                document.getElementById('cameraLoading').style.display = 'none';

                // Start scanning
                if (codeReader) {
                    codeReader.decodeFromVideoDevice(null, 'bookVideo', (result, error) => {
                        if (result) {
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

            video.style.display = 'none';
            placeholder.style.display = 'flex';
            startBtn.disabled = false;
            stopBtn.disabled = true;
        }

        function handleScanResult(scannedData) {
            console.log('Scanned:', scannedData);

            let accNo = scannedData;

            // Try to parse JSON if it's structured data
            try {
                const data = JSON.parse(scannedData);
                accNo = data.accNo || data.AccNo || data.barcode || scannedData;
            } catch (e) {
                accNo = scannedData;
            }

            document.getElementById('accNoInput').value = accNo;
            showScanResult(`Book scanned: ${accNo}`, 'success');
            searchBook();
            stopCamera();
        }

        async function searchBook() {
            const accNo = document.getElementById('accNoInput').value.trim();
            
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
</body>
</html>
