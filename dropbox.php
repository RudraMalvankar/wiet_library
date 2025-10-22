<?php
// Dropbox Book Return Page
// Students scan Book QR, then Student QR to return a book

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropbox Book Return - WIET Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        .camera-area {
            width: 100%;
            height: 240px;
            background: #fff;
            border: 2px solid #263c79;
            border-radius: 8px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        body {
            background: white;
            min-height: 100vh;
            font-family: 'Lato', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 2.5rem;
        }

        .dropbox-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            pointer-events: none;
            width: 120px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dropbox-watermark img {
            width: 120px !important;
            height: 80px !important;
            opacity: 0.15;
        }
        .dropbox-header {
            text-align: center;
            margin-bottom: 1.5rem;
            z-index: 2;
            position: relative;
        }
        .dropbox-title {
            font-family: 'Poppins', sans-serif;
            color: #263c79;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .dropbox-subtitle {
            color: #666;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        .steps-row {
            display: flex;
            flex-direction: row;
            gap: 32px;
            justify-content: center;
            width: 100%;
            margin-bottom: 1.5rem;
        }
        .step-box {
            background: #f8f9fa;
            border: 2px dashed #cfac69;
            border-radius: 8px;
            padding: 18px 10px;
            width: 350px;
            min-width: 280px;
            text-align: center;
            position: relative;
            margin-bottom: 0;
        }
        .step-title {
            color: #263c79;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            font-family: 'Poppins', sans-serif;
        }
        .scan-icon {
            font-size: 32px;
            color: #cfac69;
            margin-bottom: 10px;
        }
        .scan-btn {
            padding: 10px 22px;
            background: #263c79;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s, color 0.2s;
        }
        .scan-btn:hover {
            background: #cfac69;
            color: #263c79;
        }
        .scan-result {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            margin-top: 10px;
            display: none;
        }
        .scan-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            margin-top: 10px;
            display: none;
        }
        .return-btn {
            width: auto;
            padding: 12px 24px;
            background: #263c79;
            color: #fff;
            border: 2px solid #263c79;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s, color 0.2s;
        }
        .return-btn:hover {
            background: #cfac69;
            color: #263c79;
            border-color: #cfac69;
        }
        @media (max-width: 900px) {
            body {
                padding: 1rem 0.5rem 1.5rem 0.5rem;
                max-width: 98vw;
            }
            .steps-row {
                flex-direction: column;
                gap: 18px;
                align-items: center;
            }
            .step-box {
                width: 98vw;
                max-width: 98vw;
            }
        }
    </style>
    <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
</head>
<body>
    <div class="dropbox-watermark">
        <img src="wiet_lib/images/watumull%20logo.png" alt="Watumull Logo">
    </div>
    <div class="dropbox-header">
        <div class="dropbox-title"><i class="fas fa-inbox"></i> Dropbox Book Return</div>
        <div class="dropbox-subtitle">Scan your Book QR and Student QR to return your book</div>
    </div>
        <div class="steps-row">
            <div class="step-box" id="step1">
                <div class="step-title">Step 1: Scan Book QR</div>
                <div class="scan-icon"><i class="fas fa-book"></i></div>
                <div class="camera-area">
                    <video id="bookVideo" autoplay playsinline style="width:100%;height:100%;object-fit:cover;"></video>
                </div>
                <button class="scan-btn" onclick="startBookScan()">Scan Book QR</button>
                <div id="bookScanResult" class="scan-result"></div>
                <div id="bookScanError" class="scan-error"></div>
            </div>
            <div class="step-box" id="step2">
                <div class="step-title">Step 2: Scan Student QR</div>
                <div class="scan-icon"><i class="fas fa-user"></i></div>
                <div class="camera-area">
                    <video id="studentVideo" autoplay playsinline style="width:100%;height:100%;object-fit:cover;"></video>
                </div>
                <button class="scan-btn" onclick="startStudentScan()" disabled>Scan Student QR</button>
                <div id="studentScanResult" class="scan-result"></div>
                <div id="studentScanError" class="scan-error"></div>
            </div>
        </div>
    <button class="return-btn" id="returnBtn" disabled>Return Book</button>
    <script>
        let bookStream = null;
        let studentStream = null;
        let bookCodeReader = null;
        let studentCodeReader = null;
        let scannedBook = null;
        let scannedStudent = null;

        function startBookCamera() {
            const video = document.getElementById('bookVideo');
            navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
                video.srcObject = stream;
            }).catch(() => {
                video.poster = '';
            });
        }
        function startStudentCamera() {
            const video = document.getElementById('studentVideo');
            navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
                video.srcObject = stream;
            }).catch(() => {
                video.poster = '';
            });
        }
        function startBookScan() {
            document.getElementById('bookScanResult').style.display = 'none';
            document.getElementById('bookScanError').style.display = 'none';
            const video = document.getElementById('bookVideo');
            bookCodeReader = new ZXing.BrowserQRCodeReader();
            bookCodeReader.decodeFromVideoDevice(null, video, (result, err) => {
                if (result) {
                    scannedBook = result.text;
                    document.getElementById('bookScanResult').textContent = 'Book QR scanned: ' + scannedBook;
                    document.getElementById('bookScanResult').style.display = 'block';
                    bookCodeReader.reset();
                    document.querySelector('#step2 .scan-btn').disabled = false;
                } else if (err && !(err instanceof ZXing.NotFoundException)) {
                    document.getElementById('bookScanError').textContent = 'Error: ' + err;
                    document.getElementById('bookScanError').style.display = 'block';
                }
            });
        }
        function startStudentScan() {
            document.getElementById('studentScanResult').style.display = 'none';
            document.getElementById('studentScanError').style.display = 'none';
            const video = document.getElementById('studentVideo');
            studentCodeReader = new ZXing.BrowserQRCodeReader();
            studentCodeReader.decodeFromVideoDevice(null, video, (result, err) => {
                if (result) {
                    scannedStudent = result.text;
                    document.getElementById('studentScanResult').textContent = 'Student QR scanned: ' + scannedStudent;
                    document.getElementById('studentScanResult').style.display = 'block';
                    studentCodeReader.reset();
                    document.getElementById('returnBtn').disabled = false;
                } else if (err && !(err instanceof ZXing.NotFoundException)) {
                    document.getElementById('studentScanError').textContent = 'Error: ' + err;
                    document.getElementById('studentScanError').style.display = 'block';
                }
            });
        }
        // Start camera feeds on page load
        window.addEventListener('DOMContentLoaded', function() {
            startBookCamera();
            startStudentCamera();
        });

        document.getElementById('returnBtn').addEventListener('click', function() {
            if (scannedBook && scannedStudent) {
                alert('Book returned!\nBook QR: ' + scannedBook + '\nStudent QR: ' + scannedStudent);
                // Here, you would send scannedBook (AccNo) and scannedStudent (MemberNo) to backend for processing
                // and update DropReturn table as per ER model
                // Reset for next scan
                scannedBook = null;
                scannedStudent = null;
                document.getElementById('bookScanResult').style.display = 'none';
                document.getElementById('studentScanResult').style.display = 'none';
                document.querySelector('#step2 .scan-btn').disabled = true;
                document.getElementById('returnBtn').disabled = true;
            }
        });
    </script>
</body>
</html>
