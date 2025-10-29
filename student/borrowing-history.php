<?php
// Borrowing History Content - Complete transaction history
// This file will be included in the main content area

// Start session and check authentication
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: student_login.php');
    exit();
}

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Session variables for student info
$student_name = $_SESSION['student_name'] ?? 'Student';
$student_id = $_SESSION['student_id'] ?? null;
$member_no = $_SESSION['member_no'] ?? null;

// ============================================================
// DATA SOURCE: 100% LIVE DATABASE
// ============================================================
// ✅ Complete borrowing history - FROM DATABASE
// ✅ Statistics - CALCULATED FROM DATABASE
// ✅ Fine calculation from Return table
// ✅ All details with proper schema
// ============================================================

// Fetch complete borrowing history from database
try {
    $borrowing_history = [];
    
    // Get all circulations (active and returned) for this member
    $query = "
        SELECT 
            c.CirculationID,
            c.AccNo,
            c.IssueDate,
            c.DueDate,
            c.RenewalCount,
            r.ReturnID,
            r.ReturnDate,
            r.FineAmount,
            b.Title,
            b.Author1,
            CASE 
                WHEN r.ReturnID IS NULL THEN 'Issued'
                WHEN r.ReturnDate > c.DueDate THEN 'Returned Late'
                ELSE 'Returned'
            END as Status
        FROM Circulation c
        INNER JOIN Holding h ON c.AccNo = h.AccNo
        INNER JOIN Books b ON h.CatNo = b.CatNo
        LEFT JOIN `Return` r ON c.CirculationID = r.CirculationID
        WHERE c.MemberNo = :member_no
        ORDER BY c.IssueDate DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':member_no' => $member_no]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $borrowing_history[] = [
            'id' => 'BOR' . str_pad($row['CirculationID'], 3, '0', STR_PAD_LEFT),
            'circulation_id' => $row['CirculationID'],
            'title' => $row['Title'],
            'author' => $row['Author1'],
            'acc_no' => $row['AccNo'],
            'issue_date' => $row['IssueDate'],
            'due_date' => $row['DueDate'],
            'return_date' => $row['ReturnDate'],
            'status' => $row['Status'],
            'fine' => $row['FineAmount'] ?? 0,
            'renewed_count' => $row['RenewalCount'] ?? 0
        ];
    }
    
    // Calculate statistics from real data
    $statistics = [
        'total_borrowed' => count($borrowing_history),
        'currently_issued' => count(array_filter($borrowing_history, function ($item) {
            return $item['status'] === 'Issued';
        })),
        'total_fines_paid' => array_sum(array_column($borrowing_history, 'fine')),
        'books_renewed' => array_sum(array_column($borrowing_history, 'renewed_count'))
    ];
    
} catch (Exception $e) {
    // Fallback to empty array if database error
    $borrowing_history = [];
    $statistics = [
        'total_borrowed' => 0,
        'currently_issued' => 0,
        'total_fines_paid' => 0,
        'books_renewed' => 0
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
                            <a href="#" class="action-btn" onclick="viewBookDetails('<?php echo $record['acc_no']; ?>', '<?php echo $record['circulation_id']; ?>')">
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

<!-- Book Details Modal -->
<div id="bookDetailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 800px; margin: 50px auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="padding: 25px; border-bottom: 2px solid #cfac69;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="color: #263c79; margin: 0;">Book Details</h2>
                <button onclick="closeBookDetails()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>
        </div>
        <div id="bookDetailsContent" style="padding: 25px;">
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 40px; color: #263c79;"></i>
                <p style="margin-top: 15px; color: #666;">Loading book details...</p>
            </div>
        </div>
    </div>
</div>

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

    // View Book Details Function
    function viewBookDetails(accNo, circulationId) {
        event.preventDefault();
        
        // Show modal
        document.getElementById('bookDetailsModal').style.display = 'block';
        
        // Fetch book details via AJAX
        fetch('get_book_details.php?acc_no=' + accNo + '&circulation_id=' + circulationId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBookDetails(data.book);
                } else {
                    document.getElementById('bookDetailsContent').innerHTML = 
                        '<div style="text-align: center; padding: 40px; color: #d63384;">' +
                        '<i class="fas fa-exclamation-triangle" style="font-size: 40px;"></i>' +
                        '<p style="margin-top: 15px;">Error loading book details: ' + data.message + '</p>' +
                        '</div>';
                }
            })
            .catch(error => {
                document.getElementById('bookDetailsContent').innerHTML = 
                    '<div style="text-align: center; padding: 40px; color: #d63384;">' +
                    '<i class="fas fa-exclamation-triangle" style="font-size: 40px;"></i>' +
                    '<p style="margin-top: 15px;">Error loading book details. Please try again.</p>' +
                    '</div>';
            });
    }
    
    function displayBookDetails(book) {
        const statusClass = book.days_left <= 1 ? '#d63384' : (book.days_left <= 3 ? '#ffc107' : '#28a745');
        const statusText = book.days_left <= 0 ? 'OVERDUE' : (book.days_left <= 1 ? 'DUE TODAY' : (book.days_left <= 3 ? 'DUE SOON' : 'ACTIVE'));
        
        let html = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div style="grid-column: 1 / -1; padding: 20px; background: linear-gradient(135deg, #263c79, #1e2f5a); border-radius: 8px; color: white;">
                    <h3 style="margin: 0 0 10px 0; font-size: 24px;">${book.Title}</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9;">by ${book.Author}</p>
                    <div style="margin-top: 15px; display: inline-block; padding: 6px 12px; background: ${statusClass}; border-radius: 20px; font-size: 13px; font-weight: bold;">
                        ${statusText}
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Accession Number</div>
                    <div style="font-weight: 600; color: #263c79; font-family: monospace;">${book.AccNo}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">ISBN</div>
                    <div style="font-weight: 600; color: #263c79; font-family: monospace;">${book.ISBN || 'N/A'}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Catalog Number</div>
                    <div style="font-weight: 600; color: #263c79;">${book.CatNo || 'N/A'}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Publisher</div>
                    <div style="font-weight: 600; color: #263c79;">${book.Publisher || 'N/A'}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Edition</div>
                    <div style="font-weight: 600; color: #263c79;">${book.Edition || 'N/A'}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Location</div>
                    <div style="font-weight: 600; color: #263c79;">${book.Location}</div>
                </div>
            </div>
            
            <div style="margin-top: 25px; padding: 20px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 8px;">
                <h4 style="margin: 0 0 15px 0; color: #263c79;">
                    <i class="fas fa-calendar-alt"></i> Circulation Details
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div>
                        <div style="color: #666; font-size: 12px; margin-bottom: 3px;">Issue Date</div>
                        <div style="font-weight: 600; color: #263c79;">${book.issue_date_formatted}</div>
                    </div>
                    <div>
                        <div style="color: #666; font-size: 12px; margin-bottom: 3px;">Due Date</div>
                        <div style="font-weight: 600; color: #263c79;">${book.due_date_formatted}</div>
                    </div>
                    <div>
                        <div style="color: #666; font-size: 12px; margin-bottom: 3px;">Days Left</div>
                        <div style="font-weight: 600; color: ${statusClass};">${book.days_left <= 0 ? Math.abs(book.days_left) + ' days overdue' : book.days_left + ' days left'}</div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="padding: 15px; background: ${book.fine > 0 ? '#fff3cd' : '#d4edda'}; border-radius: 8px; border-left: 4px solid ${book.fine > 0 ? '#ffc107' : '#28a745'};">
                    <div style="color: #666; font-size: 12px; margin-bottom: 3px;">Fine Amount</div>
                    <div style="font-weight: 600; color: #263c79; font-size: 20px;">₹${book.fine}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; margin-bottom: 3px;">Renewal Count</div>
                    <div style="font-weight: 600; color: #263c79; font-size: 20px;">${book.RenewalCount} / 2</div>
                </div>
            </div>
        `;
        
        // Add return history if exists
        if (book.return_history && book.return_history.length > 0) {
            html += `
                <div style="margin-top: 25px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
                    <h4 style="margin: 0 0 15px 0; color: #263c79;">
                        <i class="fas fa-history"></i> Previous Borrowing History
                    </h4>
            `;
            
            book.return_history.forEach(history => {
                html += `
                    <div style="padding: 12px; background: white; border-radius: 6px; margin-bottom: 10px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; font-size: 13px;">
                            <div>
                                <div style="color: #666; font-size: 11px;">Issued</div>
                                <div style="font-weight: 600;">${history.issue_date}</div>
                            </div>
                            <div>
                                <div style="color: #666; font-size: 11px;">Returned</div>
                                <div style="font-weight: 600;">${history.return_date}</div>
                            </div>
                            <div>
                                <div style="color: #666; font-size: 11px;">Status</div>
                                <div style="font-weight: 600; color: ${history.fine > 0 ? '#d63384' : '#28a745'};">${history.fine > 0 ? 'Late' : 'On Time'}</div>
                            </div>
                            <div>
                                <div style="color: #666; font-size: 11px;">Fine</div>
                                <div style="font-weight: 600;">₹${history.fine}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
        }
        
        document.getElementById('bookDetailsContent').innerHTML = html;
    }
    
    function closeBookDetails() {
        document.getElementById('bookDetailsModal').style.display = 'none';
    }
    
    // Close modal on outside click
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('bookDetailsModal');
        if (event.target === modal) {
            closeBookDetails();
        }
    });

    function viewDetails(transactionId) {
        alert(`Viewing details for transaction: ${transactionId}`);
        // Here you would typically open a modal or navigate to a details page
    }
</script>
