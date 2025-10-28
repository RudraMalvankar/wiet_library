<?php
// Include AJAX handler FIRST
require_once 'ajax-handler.php';

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Fetch fine statistics
try {
    // Total pending fines
    $stmt = $pdo->query("
        SELECT SUM(r.FineAmount) as total 
        FROM `Return` r 
        INNER JOIN Circulation c ON r.CirculationID = c.CirculationID
        WHERE r.FineAmount > 0 
        AND r.FineAmount > COALESCE((SELECT SUM(PaidAmount) FROM FinePayments WHERE FinePayments.CirculationID = r.CirculationID), 0)
    ");
    $pending_fines = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total collected today
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(PaidAmount), 0) as total 
        FROM FinePayments 
        WHERE DATE(PaymentDate) = CURDATE()
    ");
    $collected_today = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total collected this month
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(PaidAmount), 0) as total 
        FROM FinePayments 
        WHERE MONTH(PaymentDate) = MONTH(CURDATE()) 
        AND YEAR(PaymentDate) = YEAR(CURDATE())
    ");
    $collected_month = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total waived
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(FineAmount - PaidAmount), 0) as total 
        FROM FinePayments 
        WHERE PaidAmount < FineAmount 
        AND Remarks LIKE '%waived%'
    ");
    $waived_total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Members with pending fines
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT c.MemberNo) as count 
        FROM `Return` r 
        INNER JOIN Circulation c ON r.CirculationID = c.CirculationID
        WHERE r.FineAmount > 0 
        AND r.FineAmount > COALESCE((SELECT SUM(PaidAmount) FROM FinePayments WHERE FinePayments.CirculationID = r.CirculationID), 0)
    ");
    $members_with_fines = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Fine stats error: " . $e->getMessage());
    $pending_fines = $collected_today = $collected_month = $waived_total = $members_with_fines = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!--  -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine Management - Library System</title>
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
            max-width: 1400px;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #263c79 0%, #3d5a9e 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-card.pending {
            background: linear-gradient(135deg, #dc3545 0%, #e55561 100%);
        }

        .stat-card.collected {
            background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
        }

        .stat-card.waived {
            background: linear-gradient(135deg, #ffc107 0%, #ffcd38 100%);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab-btn {
            padding: 12px 25px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            color: #666;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .tab-btn:hover {
            color: #263c79;
        }

        .tab-btn.active {
            color: #263c79;
            border-bottom-color: #cfac69;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 12px 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }

        .search-input:focus {
            outline: none;
            border-color: #cfac69;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #263c79;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-partial {
            background: #cce5ff;
            color: #004085;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-waived {
            background: #e2e3e5;
            color: #383d41;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .modal-header h3 {
            color: #263c79;
            font-size: 22px;
        }

        .close {
            background: none;
            border: none;
            font-size: 28px;
            color: #999;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
        }

        .form-control:focus {
            outline: none;
            border-color: #cfac69;
        }

        .form-control:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #263c79;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .receipt {
            background: white;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            font-family: monospace;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px dashed #333;
            padding-bottom: 20px;
        }

        .receipt-header h2 {
            margin: 0;
            font-size: 24px;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dotted #ccc;
        }

        .receipt-total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            margin-top: 10px;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .receipt, .receipt * {
                visibility: visible;
            }
            .receipt {
                position: absolute;
                left: 0;
                top: 0;
            }
            .btn, .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-money-bill-wave"></i> Fine Management</h1>
            <div>
                <button class="btn btn-secondary" onclick="window.location.href='dashboard.php'">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </button>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <div class="stat-number">₹<?php echo number_format($pending_fines, 2); ?></div>
                <div class="stat-label">Pending Fines</div>
            </div>
            <div class="stat-card collected">
                <div class="stat-number">₹<?php echo number_format($collected_today, 2); ?></div>
                <div class="stat-label">Collected Today</div>
            </div>
            <div class="stat-card collected">
                <div class="stat-number">₹<?php echo number_format($collected_month, 2); ?></div>
                <div class="stat-label">This Month</div>
            </div>
            <div class="stat-card waived">
                <div class="stat-number">₹<?php echo number_format($waived_total, 2); ?></div>
                <div class="stat-label">Total Waived</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $members_with_fines; ?></div>
                <div class="stat-label">Members with Fines</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('pending')">
                <i class="fas fa-exclamation-circle"></i> Pending Fines
            </button>
            <button class="tab-btn" onclick="showTab('history')">
                <i class="fas fa-history"></i> Payment History
            </button>
            <button class="tab-btn" onclick="showTab('reports')">
                <i class="fas fa-chart-bar"></i> Reports
            </button>
        </div>

        <!-- Alert Messages -->
        <div id="alertMessage" class="alert"></div>

        <!-- Pending Fines Tab -->
        <div id="pendingTab" class="tab-content active">
            <div class="search-bar">
                <input type="text" id="searchPending" class="search-input" placeholder="Search by member number, name, or book...">
                <button class="btn btn-primary" onclick="loadPendingFines()">
                    <i class="fas fa-search"></i> Search
                </button>
                <button class="btn btn-success" onclick="exportPendingFines()">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Book</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Days Overdue</th>
                            <th>Fine Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pendingFinesTable">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment History Tab -->
        <div id="historyTab" class="tab-content">
            <div class="search-bar">
                <input type="text" id="searchHistory" class="search-input" placeholder="Search payments...">
                <input type="date" id="fromDate" class="form-control" style="max-width: 200px;">
                <input type="date" id="toDate" class="form-control" style="max-width: 200px;">
                <button class="btn btn-primary" onclick="loadPaymentHistory()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Receipt No</th>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Fine Amount</th>
                            <th>Paid Amount</th>
                            <th>Payment Method</th>
                            <th>Collected By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentHistoryTable">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reportsTab" class="tab-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Daily Collection Report</div>
                    <button class="btn btn-primary" style="margin-top: 15px;" onclick="generateReport('daily')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Monthly Collection Report</div>
                    <button class="btn btn-primary" style="margin-top: 15px;" onclick="generateReport('monthly')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Defaulters Report</div>
                    <button class="btn btn-primary" style="margin-top: 15px;" onclick="generateReport('defaulters')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Waiver Report</div>
                    <button class="btn btn-primary" style="margin-top: 15px;" onclick="generateReport('waivers')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-money-bill"></i> Collect Fine Payment</h3>
                <button class="close" onclick="closePaymentModal()">&times;</button>
            </div>

            <div id="paymentInfo" class="info-grid"></div>

            <form id="paymentForm">
                <input type="hidden" id="circulationId">
                <input type="hidden" id="totalFine">

                <div class="form-group">
                    <label>Fine Amount *</label>
                    <input type="number" id="fineAmount" class="form-control" step="0.01" disabled>
                </div>

                <div class="form-group">
                    <label>Amount Paying *</label>
                    <input type="number" id="paidAmount" class="form-control" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Payment Method *</label>
                    <select id="paymentMethod" class="form-control" required>
                        <option value="">Select method</option>
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="UPI">UPI</option>
                        <option value="Net Banking">Net Banking</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Remarks (Optional)</label>
                    <textarea id="remarks" class="form-control" rows="3"></textarea>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Collect Payment
                    </button>
                    <button type="button" class="btn btn-warning" onclick="showWaiverConfirm()">
                        <i class="fas fa-hand-holding-usd"></i> Waive Fine
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receiptModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header no-print">
                <h3><i class="fas fa-receipt"></i> Payment Receipt</h3>
                <button class="close" onclick="closeReceiptModal()">&times;</button>
            </div>

            <div id="receiptContent" class="receipt"></div>

            <div class="action-buttons no-print" style="margin-top: 20px;">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <button class="btn btn-secondary" onclick="closeReceiptModal()">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentFineData = null;

        // Load pending fines
        async function loadPendingFines() {
            const search = document.getElementById('searchPending').value;
            
            try {
                const response = await fetch(`api/fines.php?action=pending&search=${encodeURIComponent(search)}`);
                const result = await response.json();

                const tbody = document.getElementById('pendingFinesTable');
                
                if (result.success && result.data && result.data.length > 0) {
                    tbody.innerHTML = result.data.map(fine => {
                        const paidAmount = parseFloat(fine.PaidAmount || 0);
                        const fineAmount = parseFloat(fine.Fine);
                        const remaining = fineAmount - paidAmount;
                        const status = remaining <= 0 ? 'paid' : paidAmount > 0 ? 'partial' : 'pending';
                        
                        return `
                            <tr>
                                <td>
                                    <strong>${fine.MemberName}</strong><br>
                                    <small>${fine.MemberNo}</small>
                                </td>
                                <td>
                                    <strong>${fine.Title}</strong><br>
                                    <small>AccNo: ${fine.AccNo}</small>
                                </td>
                                <td>${new Date(fine.IssueDate).toLocaleDateString('en-IN')}</td>
                                <td>${new Date(fine.ReturnDate).toLocaleDateString('en-IN')}</td>
                                <td>${fine.DaysOverdue || 0} days</td>
                                <td>
                                    <strong>₹${fineAmount.toFixed(2)}</strong><br>
                                    ${paidAmount > 0 ? `<small style="color: green;">Paid: ₹${paidAmount.toFixed(2)}</small>` : ''}
                                </td>
                                <td><span class="status-badge status-${status}">${status.toUpperCase()}</span></td>
                                <td>
                                    ${remaining > 0 ? `
                                        <button class="btn btn-success btn-sm" onclick='collectPayment(${JSON.stringify(fine)})'>
                                            <i class="fas fa-money-bill"></i> Collect
                                        </button>
                                    ` : `
                                        <button class="btn btn-secondary btn-sm" onclick='viewReceipt(${fine.CirculationID})'>
                                            <i class="fas fa-receipt"></i> Receipt
                                        </button>
                                    `}
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p>No pending fines found</p>
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Error loading pending fines:', error);
                showAlert('Error loading pending fines', 'error');
            }
        }

        // Collect payment
        function collectPayment(fine) {
            currentFineData = fine;
            
            const paidAmount = parseFloat(fine.PaidAmount || 0);
            const remaining = parseFloat(fine.Fine) - paidAmount;

            document.getElementById('circulationId').value = fine.CirculationID;
            document.getElementById('totalFine').value = fine.Fine;
            document.getElementById('fineAmount').value = remaining.toFixed(2);
            document.getElementById('paidAmount').value = remaining.toFixed(2);
            document.getElementById('paymentMethod').value = '';
            document.getElementById('remarks').value = '';

            document.getElementById('paymentInfo').innerHTML = `
                <div class="info-item">
                    <span class="info-label">Member</span>
                    <span class="info-value">${fine.MemberName} (${fine.MemberNo})</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Book</span>
                    <span class="info-value">${fine.Title}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Return Date</span>
                    <span class="info-value">${new Date(fine.ReturnDate).toLocaleDateString('en-IN')}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Days Overdue</span>
                    <span class="info-value">${fine.DaysOverdue || 0} days</span>
                </div>
            `;

            document.getElementById('paymentModal').classList.add('show');
        }

        // Submit payment
        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const data = {
                circulationId: document.getElementById('circulationId').value,
                fineAmount: parseFloat(document.getElementById('fineAmount').value),
                paidAmount: parseFloat(document.getElementById('paidAmount').value),
                paymentMethod: document.getElementById('paymentMethod').value,
                remarks: document.getElementById('remarks').value
            };

            try {
                const response = await fetch('api/fines.php?action=collect', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Payment collected successfully!', 'success');
                    closePaymentModal();
                    loadPendingFines();
                    
                    if (result.receiptNo) {
                        viewReceiptByNo(result.receiptNo);
                    }
                } else {
                    showAlert(result.message || 'Error collecting payment', 'error');
                }
            } catch (error) {
                console.error('Error submitting payment:', error);
                showAlert('Error processing payment', 'error');
            }
        });

        // Waive fine
        function showWaiverConfirm() {
            const amount = document.getElementById('fineAmount').value;
            if (confirm(`Are you sure you want to waive the fine of ₹${amount}? This action cannot be undone.`)) {
                waiveFine();
            }
        }

        async function waiveFine() {
            const data = {
                circulationId: document.getElementById('circulationId').value,
                fineAmount: parseFloat(document.getElementById('fineAmount').value),
                remarks: 'Fine waived by admin'
            };

            try {
                const response = await fetch('api/fines.php?action=waive', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Fine waived successfully!', 'success');
                    closePaymentModal();
                    loadPendingFines();
                } else {
                    showAlert(result.message || 'Error waiving fine', 'error');
                }
            } catch (error) {
                console.error('Error waiving fine:', error);
                showAlert('Error processing waiver', 'error');
            }
        }

        // Load payment history
        async function loadPaymentHistory() {
            const search = document.getElementById('searchHistory').value;
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;

            try {
                const response = await fetch(`api/fines.php?action=history&search=${encodeURIComponent(search)}&from=${fromDate}&to=${toDate}`);
                const result = await response.json();

                const tbody = document.getElementById('paymentHistoryTable');
                
                if (result.success && result.data && result.data.length > 0) {
                    tbody.innerHTML = result.data.map(payment => `
                        <tr>
                            <td><strong>${payment.ReceiptNo}</strong></td>
                            <td>${new Date(payment.PaymentDate).toLocaleDateString('en-IN')}</td>
                            <td>
                                ${payment.MemberName}<br>
                                <small>${payment.MemberNo}</small>
                            </td>
                            <td>₹${parseFloat(payment.FineAmount).toFixed(2)}</td>
                            <td><strong>₹${parseFloat(payment.PaidAmount).toFixed(2)}</strong></td>
                            <td>${payment.PaymentMethod}</td>
                            <td>${payment.CollectorName}</td>
                            <td>
                                <button class="btn btn-secondary btn-sm" onclick='viewReceiptByNo("${payment.ReceiptNo}")'>
                                    <i class="fas fa-receipt"></i> View
                                </button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>No payment history found</p>
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Error loading payment history:', error);
                showAlert('Error loading payment history', 'error');
            }
        }

        // View receipt
        async function viewReceipt(circulationId) {
            try {
                const response = await fetch(`api/fines.php?action=receipt&circulationId=${circulationId}`);
                const result = await response.json();

                if (result.success && result.data) {
                    displayReceipt(result.data);
                } else {
                    showAlert('Receipt not found', 'error');
                }
            } catch (error) {
                console.error('Error loading receipt:', error);
                showAlert('Error loading receipt', 'error');
            }
        }

        async function viewReceiptByNo(receiptNo) {
            try {
                const response = await fetch(`api/fines.php?action=receipt&receiptNo=${encodeURIComponent(receiptNo)}`);
                const result = await response.json();

                if (result.success && result.data) {
                    displayReceipt(result.data);
                } else {
                    showAlert('Receipt not found', 'error');
                }
            } catch (error) {
                console.error('Error loading receipt:', error);
                showAlert('Error loading receipt', 'error');
            }
        }

        // Display receipt
        function displayReceipt(data) {
            const receiptHTML = `
                <div class="receipt-header">
                    <h2>WIET College Library</h2>
                    <p>Fine Payment Receipt</p>
                    <p><strong>Receipt No: ${data.ReceiptNo}</strong></p>
                    <p>Date: ${new Date(data.PaymentDate).toLocaleString('en-IN')}</p>
                </div>
                <div class="receipt-row">
                    <span>Member:</span>
                    <span>${data.MemberName} (${data.MemberNo})</span>
                </div>
                <div class="receipt-row">
                    <span>Book:</span>
                    <span>${data.Title || 'N/A'}</span>
                </div>
                <div class="receipt-row">
                    <span>Accession No:</span>
                    <span>${data.AccNo || 'N/A'}</span>
                </div>
                <div class="receipt-row">
                    <span>Fine Amount:</span>
                    <span>₹${parseFloat(data.FineAmount).toFixed(2)}</span>
                </div>
                <div class="receipt-row receipt-total">
                    <span>Amount Paid:</span>
                    <span>₹${parseFloat(data.PaidAmount).toFixed(2)}</span>
                </div>
                <div class="receipt-row">
                    <span>Payment Method:</span>
                    <span>${data.PaymentMethod}</span>
                </div>
                <div class="receipt-row">
                    <span>Collected By:</span>
                    <span>${data.CollectorName}</span>
                </div>
                ${data.Remarks ? `
                <div class="receipt-row">
                    <span>Remarks:</span>
                    <span>${data.Remarks}</span>
                </div>
                ` : ''}
                <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px dashed #333;">
                    <p>Thank you for your payment!</p>
                    <small>This is a computer-generated receipt.</small>
                </div>
            `;

            document.getElementById('receiptContent').innerHTML = receiptHTML;
            document.getElementById('receiptModal').classList.add('show');
        }

        // Generate reports
        function generateReport(type) {
            const url = `api/fines.php?action=report&type=${type}`;
            window.open(url, '_blank');
        }

        // Export pending fines
        function exportPendingFines() {
            const url = `api/fines.php?action=export&type=pending`;
            window.open(url, '_blank');
        }

        // Tab switching
        function showTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName + 'Tab').classList.add('active');

            if (tabName === 'pending') {
                loadPendingFines();
            } else if (tabName === 'history') {
                loadPaymentHistory();
            }
        }

        // Modal controls
        function closePaymentModal() {
            document.getElementById('paymentModal').classList.remove('show');
            currentFineData = null;
        }

        function closeReceiptModal() {
            document.getElementById('receiptModal').classList.remove('show');
        }

        // Alert
        function showAlert(message, type) {
            const alert = document.getElementById('alertMessage');
            alert.textContent = message;
            alert.className = `alert alert-${type} show`;
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadPendingFines();
            
            // Set default date range (last 30 days)
            const today = new Date();
            const lastMonth = new Date();
            lastMonth.setDate(lastMonth.getDate() - 30);
            
            document.getElementById('toDate').valueAsDate = today;
            document.getElementById('fromDate').valueAsDate = lastMonth;
        });

        // Make functions global
        window.showTab = showTab;
        window.collectPayment = collectPayment;
        window.viewReceipt = viewReceipt;
        window.viewReceiptByNo = viewReceiptByNo;
        window.closePaymentModal = closePaymentModal;
        window.closeReceiptModal = closeReceiptModal;
        window.showWaiverConfirm = showWaiverConfirm;
        window.loadPendingFines = loadPendingFines;
        window.loadPaymentHistory = loadPaymentHistory;
        window.generateReport = generateReport;
        window.exportPendingFines = exportPendingFines;
    </script>
</body>
</html>
