<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Fetch inventory statistics
try {
    // Total books count
    $stmt = $pdo->query("SELECT COUNT(DISTINCT CatNo) as total FROM Books");
    $total_books = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total copies (holdings)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding");
    $total_copies = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Available copies
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE Status = 'Available'");
    $available_copies = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Issued copies
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE Status = 'Issued'");
    $issued_copies = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Lost/Damaged copies
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Holding WHERE Status IN ('Lost', 'Damaged')");
    $lost_damaged = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Books with low stock (< 2 available copies)
    $stmt = $pdo->query("
        SELECT COUNT(*) as total FROM (
            SELECT CatNo, COUNT(*) as available_count 
            FROM Holding 
            WHERE Status = 'Available' 
            GROUP BY CatNo 
            HAVING available_count < 2
        ) as low_stock
    ");
    $low_stock = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Inventory stats error: " . $e->getMessage());
    $total_books = $total_copies = $available_copies = $issued_copies = $lost_damaged = $low_stock = 0;
}

// Fetch distinct locations
try {
    $stmt = $pdo->query("SELECT DISTINCT Location FROM Holding WHERE Location IS NOT NULL AND Location != '' ORDER BY Location");
    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Locations fetch error: " . $e->getMessage());
    $locations = [];
}

// Fetch distinct subjects
try {
    $stmt = $pdo->query("SELECT DISTINCT Subject FROM Books WHERE Subject IS NOT NULL AND Subject != '' ORDER BY Subject");
    $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Subjects fetch error: " . $e->getMessage());
    $subjects = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - WIET Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            color: #263c79;
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title i {
            color: #cfac69;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #263c79;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1e2d5f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(38, 60, 121, 0.3);
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #cfac69;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card.total {
            border-left-color: #263c79;
        }

        .stat-card.available {
            border-left-color: #28a745;
        }

        .stat-card.issued {
            border-left-color: #17a2b8;
        }

        .stat-card.alert {
            border-left-color: #dc3545;
        }

        .stat-card.warning {
            border-left-color: #ffc107;
        }

        .stat-icon {
            font-size: 36px;
            margin-bottom: 12px;
        }

        .stat-card.total .stat-icon {
            color: #263c79;
        }

        .stat-card.available .stat-icon {
            color: #28a745;
        }

        .stat-card.issued .stat-icon {
            color: #17a2b8;
        }

        .stat-card.alert .stat-icon {
            color: #dc3545;
        }

        .stat-card.warning .stat-icon {
            color: #ffc107;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #263c79;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        /* Search Section */
        .search-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid #263c79;
        }

        .section-header {
            background: linear-gradient(135deg, #263c79 0%, #1a2d5a 100%);
            color: white;
            padding: 15px 20px;
            margin: -25px -25px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px solid #cfac69;
        }

        .section-header i {
            color: #cfac69;
        }

        .search-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
            flex: 1;
        }

        .form-group label {
            font-weight: 600;
            color: #263c79;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-group label i {
            color: #cfac69;
        }

        .form-control {
            padding: 10px 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #cfac69;
            box-shadow: 0 0 0 3px rgba(207, 172, 105, 0.2);
        }

        /* Results Table */
        .results-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid #263c79;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }

        .inventory-table thead {
            background: linear-gradient(135deg, #263c79 0%, #1a2d5a 100%);
            color: white;
        }

        .inventory-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 3px solid #cfac69;
        }

        .inventory-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .inventory-table tbody tr:hover {
            background: rgba(207, 172, 105, 0.1);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-available {
            background-color: #d4edda;
            color: #155724;
        }

        .status-issued {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-lost {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-damaged {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-maintenance {
            background-color: #e7e7e7;
            color: #383838;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            margin-bottom: 10px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .page-btn {
            padding: 8px 12px;
            border: 2px solid #263c79;
            background: white;
            color: #263c79;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .page-btn:hover {
            background: #263c79;
            color: white;
        }

        .page-btn.active {
            background: #263c79;
            color: white;
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .inventory-table {
                font-size: 12px;
            }

            .inventory-table th,
            .inventory-table td {
                padding: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-boxes"></i>
                Inventory Management
            </h1>
            <div class="action-buttons">
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
                <button class="btn btn-success" onclick="exportInventory()">
                    <i class="fas fa-file-excel"></i>
                    Export Report
                </button>
                <button class="btn btn-warning" onclick="printInventory()">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_books); ?></div>
                <div class="stat-label">Total Books</div>
            </div>
            <div class="stat-card available">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_copies); ?></div>
                <div class="stat-label">Total Copies</div>
            </div>
            <div class="stat-card available">
                <div class="stat-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
                <div class="stat-number"><?php echo number_format($available_copies); ?></div>
                <div class="stat-label">Available</div>
            </div>
            <div class="stat-card issued">
                <div class="stat-icon">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="stat-number"><?php echo number_format($issued_copies); ?></div>
                <div class="stat-label">Issued</div>
            </div>
            <div class="stat-card alert">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-number"><?php echo number_format($lost_damaged); ?></div>
                <div class="stat-label">Lost/Damaged</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-battery-quarter"></i>
                </div>
                <div class="stat-number"><?php echo number_format($low_stock); ?></div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <div class="section-header">
                <i class="fas fa-search"></i>
                <h3 style="margin: 0;">Search Inventory</h3>
            </div>
            <form id="searchForm" onsubmit="searchInventory(); return false;">
                <div class="search-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-book"></i>
                            Title / ISBN
                        </label>
                        <input type="text" id="searchKeyword" class="form-control" placeholder="Enter title, ISBN, or keywords...">
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-bookmark"></i>
                            Status
                        </label>
                        <select id="searchStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="Available">Available</option>
                            <option value="Issued">Issued</option>
                            <option value="Lost">Lost</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-map-marker-alt"></i>
                            Location
                        </label>
                        <select id="searchLocation" class="form-control">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location); ?>">
                                    <?php echo htmlspecialchars($location); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-tag"></i>
                            Subject
                        </label>
                        <select id="searchSubject" class="form-control">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo htmlspecialchars($subject); ?>">
                                    <?php echo htmlspecialchars($subject); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="section-header">
                <i class="fas fa-list"></i>
                <h3 style="margin: 0;">Inventory Results</h3>
            </div>
            <div id="inventoryResults">
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>Search Inventory</h3>
                    <p>Use the search filters above to find books and check inventory status</p>
                </div>
            </div>
            <div id="paginationContainer"></div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        const pageSize = 20;

        function searchInventory(page = 1) {
            currentPage = page;
            const keyword = document.getElementById('searchKeyword').value.trim();
            const status = document.getElementById('searchStatus').value;
            const location = document.getElementById('searchLocation').value;
            const subject = document.getElementById('searchSubject').value;

            // Build query parameters
            const params = new URLSearchParams({
                action: 'search_inventory',
                page: page,
                pageSize: pageSize
            });

            if (keyword) params.append('keyword', keyword);
            if (status) params.append('status', status);
            if (location) params.append('location', location);
            if (subject) params.append('subject', subject);

            // Show loading
            document.getElementById('inventoryResults').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <h3>Loading...</h3>
                    <p>Searching inventory records...</p>
                </div>
            `;

            // Fetch results
            fetch(`api/books.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayResults(data.data, data.total, data.page, data.pageSize);
                    } else {
                        throw new Error(data.message || 'Search failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('inventoryResults').innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-circle" style="color: #dc3545;"></i>
                            <h3>Error</h3>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }

        function displayResults(data, total, page, pageSize) {
            const resultsContainer = document.getElementById('inventoryResults');
            
            if (!data || data.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Results Found</h3>
                        <p>No inventory records match your search criteria</p>
                    </div>
                `;
                document.getElementById('paginationContainer').innerHTML = '';
                return;
            }

            let tableHTML = `
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>CatNo</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Subject</th>
                            <th>Total Copies</th>
                            <th>Available</th>
                            <th>Issued</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.forEach(book => {
                const availableCopies = book.AvailableCopies || 0;
                const totalCopies = book.TotalCopies || 0;
                const issuedCopies = book.IssuedCopies || 0;

                tableHTML += `
                    <tr>
                        <td><strong>${book.CatNo}</strong></td>
                        <td>${book.Title || '-'}</td>
                        <td>${book.Author1 || '-'}</td>
                        <td>${book.ISBN || '-'}</td>
                        <td>${book.Subject || '-'}</td>
                        <td><strong>${totalCopies}</strong></td>
                        <td>
                            <span class="status-badge status-available">${availableCopies}</span>
                        </td>
                        <td>
                            <span class="status-badge status-issued">${issuedCopies}</span>
                        </td>
                        <td>
                            <button class="btn btn-primary" onclick="viewDetails(${book.CatNo})" style="padding: 5px 10px; font-size: 12px;">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                        </td>
                    </tr>
                `;
            });

            tableHTML += `
                    </tbody>
                </table>
            `;

            resultsContainer.innerHTML = tableHTML;
            renderPagination(total, page, pageSize);
        }

        function renderPagination(total, currentPage, pageSize) {
            const totalPages = Math.ceil(total / pageSize);
            if (totalPages <= 1) {
                document.getElementById('paginationContainer').innerHTML = '';
                return;
            }

            let paginationHTML = '<div class="pagination">';
            
            // Previous button
            paginationHTML += `
                <button class="page-btn" onclick="searchInventory(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;

            // Page numbers (show 5 pages max)
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                paginationHTML += `<button class="page-btn" onclick="searchInventory(1)">1</button>`;
                if (startPage > 2) {
                    paginationHTML += `<span style="padding: 0 5px;">...</span>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationHTML += `
                    <button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="searchInventory(${i})">
                        ${i}
                    </button>
                `;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHTML += `<span style="padding: 0 5px;">...</span>`;
                }
                paginationHTML += `<button class="page-btn" onclick="searchInventory(${totalPages})">${totalPages}</button>`;
            }

            // Next button
            paginationHTML += `
                <button class="page-btn" onclick="searchInventory(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;

            paginationHTML += '</div>';
            document.getElementById('paginationContainer').innerHTML = paginationHTML;
        }

        function viewDetails(catNo) {
            window.location.href = `books-management.php?view=${catNo}`;
        }

        function exportInventory() {
            alert('Export functionality will generate Excel/CSV report of current inventory');
        }

        function printInventory() {
            window.print();
        }

        // Load initial data on page load
        document.addEventListener('DOMContentLoaded', function() {
            searchInventory(1);
        });
    </script>
</body>

</html>
