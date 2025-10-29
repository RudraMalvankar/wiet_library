<?php
// My Books Content - Currently issued books with renewal functionality
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
// ✅ Currently issued books - FROM DATABASE
// ✅ Book details with ISBN from Books table
// ✅ Renewal count and eligibility from Circulation table
// ✅ Fine calculation from Member table (FinePerDay)
// ============================================================

// Fetch real issued books from database
try {
    $activeCirculations = getMemberActiveCirculations($pdo, $member_no);
    
    $issued_books = [];
    foreach ($activeCirculations as $circ) {
        $daysLeft = ceil((strtotime($circ['DueDate']) - time()) / (60 * 60 * 24));
        $fine = 0;
        
        if ($daysLeft < 0) {
            // Calculate fine for overdue books
            $member = getMemberByNo($pdo, $member_no);
            $fine = abs($daysLeft) * ($member['FinePerDay'] ?? 2.00);
        }
        
        $issued_books[] = [
            'acc_no' => $circ['AccNo'],
            'title' => $circ['Title'],
            'author' => $circ['Author1'],
            'isbn' => $circ['ISBN'] ?? 'N/A',
            'issue_date' => $circ['IssueDate'],
            'due_date' => $circ['DueDate'],
            'days_left' => $daysLeft,
            'renewable' => ($circ['RenewalCount'] ?? 0) < 2, // Max 2 renewals
            'fine' => $fine,
            'circulation_id' => $circ['CirculationID']
        ];
    }
    
} catch (Exception $e) {
    // Fallback to empty array if database error
    $issued_books = [];
}

// Library rules (static configuration)
$library_rules = [
    'max_books' => 5,
    'loan_period' => 21,
    'renewal_limit' => 2,
    'fine_per_day' => 2
];
?>

<style>
    /* My Books specific styles */
    .books-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .books-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }

    .books-summary {
        text-align: right;
        color: #666;
    }

    .summary-stat {
        display: block;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .books-table-container {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
    }

    .books-table {
        width: 100%;
        border-collapse: collapse;
    }

    .books-table th {
        background: #f8f9fa;
        color: #263c79;
        font-weight: 600;
        padding: 15px 12px;
        text-align: left;
        border-bottom: 2px solid #e0e0e0;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .books-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: top;
    }

    .books-table tr:last-child td {
        border-bottom: none;
    }

    .books-table tr:hover {
        background: #f8f9fa;
    }

    .book-title {
        font-weight: 600;
        color: #263c79;
        margin-bottom: 3px;
        font-size: 15px;
    }

    .book-author {
        color: #666;
        font-size: 13px;
        margin-bottom: 2px;
    }

    .book-isbn {
        color: #888;
        font-size: 12px;
        font-family: monospace;
    }

    .acc-number {
        font-family: monospace;
        font-weight: 600;
        color: #263c79;
        background: #f0f2f5;
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 12px;
    }

    .date-info {
        font-size: 13px;
        line-height: 1.4;
    }

    .issue-date {
        color: #666;
        margin-bottom: 2px;
    }

    .due-date {
        font-weight: 600;
        color: #263c79;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
        margin-bottom: 8px;
    }

    .status-urgent {
        background: #ffe6e6;
        color: #d63384;
    }

    .status-warning {
        background: #fff3cd;
        color: #856404;
    }

    .status-good {
        background: #d4edda;
        color: #155724;
    }

    .days-left {
        font-size: 12px;
        color: #666;
    }

    .fine-amount {
        font-weight: 600;
        color: #d63384;
        font-size: 14px;
    }

    .no-fine {
        color: #28a745;
        font-size: 12px;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .renew-btn {
        background: #263c79;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .renew-btn:hover:not(:disabled) {
        background: #1e2f5a;
    }

    .renew-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .view-btn {
        background: transparent;
        color: #263c79;
        border: 1px solid #263c79;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
    }

    .view-btn:hover {
        background: #263c79;
        color: white;
        text-decoration: none;
    }

    .library-rules {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        
    }

    .rules-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        
    }

    .rules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .rule-item {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        text-align: center;
        border-left: 4px solid #cfac69;
    }

    .rule-value {
        font-size: 20px;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 5px;
    }

    .rule-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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

    .search-books-btn {
        background: #263c79;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        transition: background 0.3s ease;
    }

    .search-books-btn:hover {
        background: #1e2f5a;
        text-decoration: none;
        color: white;
    }

    @media (max-width: 768px) {
        .books-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .books-summary {
            text-align: left;
        }

        .books-table-container {
            overflow-x: auto;
        }

        .books-table {
            min-width: 600px;
        }

        .rules-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="books-header">
    <h1 class="books-title">My Books</h1>
    <div class="books-summary">
        <span class="summary-stat"><strong><?php echo count($issued_books); ?></strong> books issued</span>
        <span class="summary-stat"><strong><?php echo $library_rules['max_books'] - count($issued_books); ?></strong> more available</span>
    </div>
</div>

<?php if (empty($issued_books)): ?>
    <div class="books-table-container">
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>No Books Currently Issued</h3>
            <p>You haven't borrowed any books yet. Start exploring our collection!</p>
            <a href="#" class="search-books-btn" data-page="search-books">
                <i class="fas fa-search"></i>
                Search Books
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="books-table-container">
        <table class="books-table">
            <thead>
                <tr>
                    <th>Book Details</th>
                    <th>Acc. No.</th>
                    <th>Issue & Due Date</th>
                    <th>Status</th>
                    <th>Fine</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issued_books as $book): ?>
                    <tr>
                        <td>
                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                            <div class="book-isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></div>
                        </td>
                        <td>
                            <span class="acc-number"><?php echo htmlspecialchars($book['acc_no']); ?></span>
                        </td>
                        <td>
                            <div class="date-info">
                                <div class="issue-date">Issued: <?php echo date('M j, Y', strtotime($book['issue_date'])); ?></div>
                                <div class="due-date">Due: <?php echo date('M j, Y', strtotime($book['due_date'])); ?></div>
                            </div>
                        </td>
                        <td>
                            <?php
                            $status_class = $book['days_left'] <= 1 ? 'status-urgent' : ($book['days_left'] <= 3 ? 'status-warning' : 'status-good');
                            $status_text = $book['days_left'] <= 0 ? 'OVERDUE' : ($book['days_left'] <= 1 ? 'DUE TODAY' : ($book['days_left'] <= 3 ? 'DUE SOON' : 'ACTIVE'));
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            <div class="days-left">
                                <?php
                                if ($book['days_left'] <= 0) {
                                    echo abs($book['days_left']) . ' days overdue';
                                } else {
                                    echo $book['days_left'] . ' days left';
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($book['fine'] > 0): ?>
                                <div class="fine-amount">₹<?php echo $book['fine']; ?></div>
                            <?php else: ?>
                                <div class="no-fine">No Fine</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($book['renewable']): ?>
                                    <button class="renew-btn" onclick="renewBook('<?php echo $book['acc_no']; ?>')">
                                        <i class="fas fa-redo"></i> Renew
                                    </button>
                                <?php else: ?>
                                    <button class="renew-btn" disabled title="Renewal not available">
                                        <i class="fas fa-ban"></i> Cannot Renew
                                    </button>
                                <?php endif; ?>
                                <a href="#" class="view-btn" onclick="viewBookDetails('<?php echo $book['acc_no']; ?>', '<?php echo $book['circulation_id']; ?>')">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Library Rules -->
<div class="library-rules">
    <h3 class="rules-title">
        <i class="fas fa-info-circle"></i>
        Library Rules & Policies
    </h3>
    <div class="rules-grid">
        <div class="rule-item">
            <div class="rule-value"><?php echo $library_rules['max_books']; ?></div>
            <div class="rule-label">Maximum Books</div>
        </div>
        <div class="rule-item">
            <div class="rule-value"><?php echo $library_rules['loan_period']; ?> days</div>
            <div class="rule-label">Loan Period</div>
        </div>
        <div class="rule-item">
            <div class="rule-value"><?php echo $library_rules['renewal_limit']; ?> times</div>
            <div class="rule-label">Renewal Limit</div>
        </div>
        <div class="rule-item">
            <div class="rule-value">₹<?php echo $library_rules['fine_per_day']; ?></div>
            <div class="rule-label">Fine per Day</div>
        </div>
    </div>
</div>

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
                    <h3 style="margin: 0 0 10px 0; font-size: 24px;">${book.title}</h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9;">by ${book.author}</p>
                    <div style="margin-top: 15px; display: inline-block; padding: 6px 12px; background: ${statusClass}; border-radius: 20px; font-size: 13px; font-weight: bold;">
                        ${statusText}
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Accession Number</div>
                    <div style="font-weight: 600; color: #263c79; font-family: monospace;">${book.acc_no}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">ISBN</div>
                    <div style="font-weight: 600; color: #263c79; font-family: monospace;">${book.isbn}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Catalog Number</div>
                    <div style="font-weight: 600; color: #263c79;">${book.CatNo || 'N/A'}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Publisher</div>
                    <div style="font-weight: 600; color: #263c79;">${book.publisher || 'N/A'}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Edition</div>
                    <div style="font-weight: 600; color: #263c79;">${book.edition || 'N/A'}</div>
                </div>
                
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Location</div>
                    <div style="font-weight: 600; color: #263c79;">${book.location}</div>
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
                    <div style="font-weight: 600; color: #263c79; font-size: 20px;">${book.renewal_count} / 2</div>
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

    function renewBook(accNo) {
        if (confirm('Are you sure you want to renew this book?')) {
            // Show loading state
            event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Renewing...';
            event.target.disabled = true;

            // Simulate API call - replace with actual AJAX request
            setTimeout(() => {
                alert('Book renewed successfully! New due date: ' + new Date(Date.now() + 21 * 24 * 60 * 60 * 1000).toLocaleDateString());
                // Reload the page or update the table
                location.reload();
            }, 1500);
        }
    }

    // Add click handler for search books button
    document.addEventListener('DOMContentLoaded', function() {
        const searchBtn = document.querySelector('.search-books-btn[data-page]');
        if (searchBtn) {
            searchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');

                // Update sidebar active state
                document.querySelectorAll('.sidebar-link').forEach(link => {
                    link.classList.remove('active');
                });

                const sidebarLink = document.querySelector(`.sidebar-link[data-page="${page}"]`);
                if (sidebarLink) {
                    sidebarLink.classList.add('active');
                }

                // Load the page
                if (window.loadPage) {
                    window.loadPage(page);
                }
            });
        }
    });
</script>
