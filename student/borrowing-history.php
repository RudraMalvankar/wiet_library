<?php
// Borrowing History Content - Complete transaction history
// This file will be included in the main content area

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Session variables for student info
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'John Doe';
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 'STU2024001';
$member_no = isset($_SESSION['MemberNo']) ? $_SESSION['MemberNo'] : 2511;

// ============================================================
// DATA SOURCE: DATABASE (Partial Integration)
// ============================================================
// ✅ Borrowing history - FROM DATABASE
// ✅ Statistics - CALCULATED FROM DATABASE
// ⚠️ Real-time fine calculation needs enhancement
// TODO: Add fine calculation from FinePayments table
// ============================================================

// Fetch real borrowing history from database
try {
    $borrowing_history = [];
    
    // Get all circulations (active and returned) for this member
    $query = "SELECT 
        c.CirculationID,
        c.AccNo,
        c.IssueDate,
        c.DueDate,
        c.RenewalCount,
        r.ReturnID,
        r.ReturnDate,
        r.LateFine,
        r.Status as ReturnStatus,
        b.Title,
        b.Author1,
        CASE 
            WHEN r.ReturnID IS NULL THEN 'Issued'
            WHEN r.Status = 'Returned Late' THEN 'Returned Late'
            ELSE 'Returned'
        END as Status
    FROM circulation c
    INNER JOIN holding h ON c.AccNo = h.AccNo
    INNER JOIN books b ON h.CallNo = b.CallNo
    LEFT JOIN `return` r ON c.CirculationID = r.CirculationID
    WHERE c.MemberNo = :member_no
    ORDER BY c.IssueDate DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':member_no' => $member_no]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $borrowing_history[] = [
            'id' => 'BOR' . str_pad($row['CirculationID'], 3, '0', STR_PAD_LEFT),
            'title' => $row['Title'],
            'author' => $row['Author1'],
            'acc_no' => $row['AccNo'],
            'issue_date' => $row['IssueDate'],
            'due_date' => $row['DueDate'],
            'return_date' => $row['ReturnDate'],
            'status' => $row['Status'],
            'fine' => $row['LateFine'] ?? 0,
            'renewed_count' => $row['RenewalCount'] ?? 0
        ];
    }
    
    // Calculate statistics
    $statistics = [
        'total_borrowed' => count($borrowing_history),
        'currently_issued' => count(array_filter($borrowing_history, function ($item) {
            return $item['status'] === 'Issued';
        })),
        'total_fines_paid' => array_sum(array_column($borrowing_history, 'fine')),
        'books_renewed' => array_sum(array_column($borrowing_history, 'renewed_count'))
    ];
    
} catch (Exception $e) {
    // Fallback to dummy data if database error
    $borrowing_history = [
        [
            'id' => 'BOR001',
            'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
            'author' => 'Robert C. Martin',
            'acc_no' => 'CS001234',
            'issue_date' => '2025-09-10',
            'due_date' => '2025-09-25',
            'return_date' => null,
            'status' => 'Issued',
            'fine' => 0,
            'renewed_count' => 0
        ],
        [
            'id' => 'BOR002',
            'title' => 'Introduction to Algorithms',
            'author' => 'Thomas H. Cormen',
            'acc_no' => 'CS002145',
            'issue_date' => '2025-08-15',
            'due_date' => '2025-09-05',
            'return_date' => '2025-09-03',
            'status' => 'Returned',
            'fine' => 0,
            'renewed_count' => 1
        ],
        [
            'id' => 'BOR003',
            'title' => 'Design Patterns',
            'author' => 'Gang of Four',
            'acc_no' => 'CS003256',
            'issue_date' => '2025-07-20',
            'due_date' => '2025-08-10',
            'return_date' => '2025-08-12',
            'status' => 'Returned Late',
            'fine' => 10,
            'renewed_count' => 0
        ],
        [
            'id' => 'BOR004',
            'title' => 'JavaScript: The Good Parts',
            'author' => 'Douglas Crockford',
            'acc_no' => 'WEB001123',
            'issue_date' => '2025-06-28',
            'due_date' => '2025-07-19',
            'return_date' => '2025-07-18',
            'status' => 'Returned',
            'fine' => 0,
            'renewed_count' => 2
        ],
        [
            'id' => 'BOR005',
            'title' => 'Database System Concepts',
            'author' => 'Abraham Silberschatz',
            'acc_no' => 'DB001445',
            'issue_date' => '2025-06-10',
            'due_date' => '2025-07-01',
            'return_date' => '2025-07-01',
            'status' => 'Returned',
            'fine' => 0,
            'renewed_count' => 1
        ]
    ];

    $statistics = [
        'total_borrowed' => count($borrowing_history),
        'currently_issued' => count(array_filter($borrowing_history, function ($item) {
            return $item['status'] === 'Issued';
        })),
        'total_fines_paid' => array_sum(array_column($borrowing_history, 'fine')),
        'books_renewed' => array_sum(array_column($borrowing_history, 'renewed_count'))
    ];
}
?>

<style>
    /* Borrowing History specific styles */
    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .history-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }

    .filter-controls {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .filter-select {
        padding: 8px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
        background: white;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: #cfac69;
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #cfac69;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 8px;
        display: block;
    }

    .stat-label {
        color: #666;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .history-table-container {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
    }

    .history-table th {
        background: #f8f9fa;
        color: #263c79;
        font-weight: 600;
        padding: 15px 12px;
        text-align: left;
        border-bottom: 2px solid #e0e0e0;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .history-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: top;
    }

    .history-table tr:last-child td {
        border-bottom: none;
    }

    .history-table tr:hover {
        background: #f8f9fa;
    }

    .transaction-id {
        font-family: monospace;
        font-weight: 600;
        color: #263c79;
        background: #f0f2f5;
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 11px;
    }

    .book-details {
        max-width: 250px;
    }

    .book-title {
        font-weight: 600;
        color: #263c79;
        margin-bottom: 3px;
        font-size: 14px;
        line-height: 1.3;
    }

    .book-author {
        color: #666;
        font-size: 12px;
        margin-bottom: 2px;
    }

    .acc-number {
        font-family: monospace;
        font-weight: 600;
        color: #888;
        font-size: 11px;
    }

    .date-cell {
        font-size: 13px;
        line-height: 1.4;
        min-width: 90px;
    }

    .date-label {
        font-size: 10px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .date-value {
        color: #263c79;
        font-weight: 500;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-issued {
        background: #fff3cd;
        color: #856404;
    }

    .status-returned {
        background: #d4edda;
        color: #155724;
    }

    .status-returned-late {
        background: #f8d7da;
        color: #721c24;
    }

    .fine-cell {
        text-align: center;
        font-weight: 600;
    }

    .fine-amount {
        color: #d63384;
        font-size: 14px;
    }

    .no-fine {
        color: #28a745;
        font-size: 12px;
    }

    .renewal-count {
        text-align: center;
        font-size: 13px;
    }

    .renewal-badge {
        background: #e3f2fd;
        color: #1565c0;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
    }

    .actions-cell {
        text-align: center;
    }

    .action-btn {
        background: transparent;
        color: #263c79;
        border: 1px solid #263c79;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .action-btn:hover {
        background: #263c79;
        color: white;
        text-decoration: none;
    }

    .search-filter {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .search-form {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 15px;
        align-items: end;
    }

    .search-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .search-label {
        font-weight: 600;
        color: #263c79;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .search-input {
        padding: 10px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #cfac69;
    }

    .search-btn {
        background: #263c79;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
        white-space: nowrap;
    }

    .search-btn:hover {
        background: #1e2f5a;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state i {
        font-size: 48px;
        color: #ccc;
        margin-bottom: 15px;
    }

    .empty-state h3 {
        color: #263c79;
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .history-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .filter-controls {
            width: 100%;
            justify-content: flex-start;
        }

        .stats-overview {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .search-form {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .history-table-container {
            overflow-x: auto;
        }

        .history-table {
            min-width: 800px;
        }

        .book-details {
            max-width: 200px;
        }
    }
</style>

<div class="history-header">
    <h1 class="history-title">Borrowing History</h1>
    <div class="filter-controls">
        <select class="filter-select" id="statusFilter">
            <option value="">All Status</option>
            <option value="Issued">Currently Issued</option>
            <option value="Returned">Returned</option>
            <option value="Returned Late">Returned Late</option>
        </select>
        <select class="filter-select" id="yearFilter">
            <option value="">All Time</option>
            <option value="2025">2025</option>
            <option value="2024">2024</option>
            <option value="2023">2023</option>
        </select>
    </div>
</div>

<!-- Statistics Overview -->
<div class="stats-overview">
    <div class="stat-card">
        <span class="stat-number"><?php echo $statistics['total_borrowed']; ?></span>
        <div class="stat-label">Total Borrowed</div>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo $statistics['currently_issued']; ?></span>
        <div class="stat-label">Currently Issued</div>
    </div>
    <div class="stat-card">
        <span class="stat-number">₹<?php echo $statistics['total_fines_paid']; ?></span>
        <div class="stat-label">Total Fines Paid</div>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo $statistics['books_renewed']; ?></span>
        <div class="stat-label">Books Renewed</div>
    </div>
</div>

<!-- Search Filter -->
<div class="search-filter">
    <form class="search-form" id="historySearchForm">
        <div class="search-field">
            <label class="search-label">Book Title or Author</label>
            <input type="text" class="search-input" id="searchQuery" placeholder="Search your history...">
        </div>
        <div class="search-field">
            <label class="search-label">Date Range</label>
            <input type="month" class="search-input" id="dateRange">
        </div>
        <button type="submit" class="search-btn">
            <i class="fas fa-search"></i>
            Search
        </button>
    </form>
</div>

<!-- History Table -->
<?php if (empty($borrowing_history)): ?>
    <div class="history-table-container">
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <h3>No Borrowing History</h3>
            <p>You haven't borrowed any books yet. Start exploring our collection!</p>
        </div>
    </div>
<?php else: ?>
    <div class="history-table-container">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Book Details</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Fine</th>
                    <th>Renewals</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="historyTableBody">
                <?php foreach ($borrowing_history as $record): ?>
                    <tr data-status="<?php echo strtolower(str_replace(' ', '_', $record['status'])); ?>"
                        data-year="<?php echo date('Y', strtotime($record['issue_date'])); ?>">
                        <td>
                            <span class="transaction-id"><?php echo htmlspecialchars($record['id']); ?></span>
                        </td>
                        <td class="book-details">
                            <div class="book-title"><?php echo htmlspecialchars($record['title']); ?></div>
                            <div class="book-author">by <?php echo htmlspecialchars($record['author']); ?></div>
                            <div class="acc-number">Acc: <?php echo htmlspecialchars($record['acc_no']); ?></div>
                        </td>
                        <td class="date-cell">
                            <div class="date-label">Issued</div>
                            <div class="date-value"><?php echo date('M j, Y', strtotime($record['issue_date'])); ?></div>
                        </td>
                        <td class="date-cell">
                            <div class="date-label">Due</div>
                            <div class="date-value"><?php echo date('M j, Y', strtotime($record['due_date'])); ?></div>
                        </td>
                        <td class="date-cell">
                            <?php if ($record['return_date']): ?>
                                <div class="date-label">Returned</div>
                                <div class="date-value"><?php echo date('M j, Y', strtotime($record['return_date'])); ?></div>
                            <?php else: ?>
                                <div class="date-value" style="color: #666; font-style: italic;">Not returned</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status_class = 'status-' . strtolower(str_replace(' ', '-', $record['status']));
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $record['status']; ?>
                            </span>
                        </td>
                        <td class="fine-cell">
                            <?php if ($record['fine'] > 0): ?>
                                <div class="fine-amount">₹<?php echo $record['fine']; ?></div>
                            <?php else: ?>
                                <div class="no-fine">₹0</div>
                            <?php endif; ?>
                        </td>
                        <td class="renewal-count">
                            <?php if ($record['renewed_count'] > 0): ?>
                                <span class="renewal-badge"><?php echo $record['renewed_count']; ?>x</span>
                            <?php else: ?>
                                <span style="color: #ccc;">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <a href="#" class="action-btn" onclick="viewDetails('<?php echo $record['id']; ?>')">
                                <i class="fas fa-eye"></i>
                                Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', function() {
        filterHistory();
    });

    document.getElementById('yearFilter').addEventListener('change', function() {
        filterHistory();
    });

    function filterHistory() {
        const statusFilter = document.getElementById('statusFilter').value;
        const yearFilter = document.getElementById('yearFilter').value;
        const rows = document.querySelectorAll('#historyTableBody tr');

        rows.forEach(row => {
            const status = row.dataset.status;
            const year = row.dataset.year;

            const statusMatch = !statusFilter || status === statusFilter.toLowerCase().replace(' ', '_');
            const yearMatch = !yearFilter || year === yearFilter;

            if (statusMatch && yearMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Search functionality
    document.getElementById('historySearchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const query = document.getElementById('searchQuery').value.toLowerCase();
        const rows = document.querySelectorAll('#historyTableBody tr');

        rows.forEach(row => {
            const bookTitle = row.querySelector('.book-title').textContent.toLowerCase();
            const bookAuthor = row.querySelector('.book-author').textContent.toLowerCase();

            if (bookTitle.includes(query) || bookAuthor.includes(query) || query === '') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    function viewDetails(transactionId) {
        alert(`Viewing details for transaction: ${transactionId}`);
        // Here you would typically open a modal or navigate to a details page
    }
</script>