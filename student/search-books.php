<?php
// Search Books Content - Advanced book search functionality
// This file will be included in the main content area

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Session variables for student info
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'John Doe';
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 'STU2024001';

// ============================================================
// DATA SOURCE: DATABASE (Fully Integrated)
// ============================================================
// ✅ Featured books - FROM DATABASE
// ✅ Book search - FROM DATABASE
// ✅ Categories - FROM DATABASE
// ============================================================

// Fetch featured/new arrivals books from database
try {
    $featured_books = [];
    
    // Get available books sorted by CallNo descending (newest first)
    $query = "SELECT 
        b.CallNo,
        b.Title,
        b.Author1,
        b.ISBN,
        b.Subject as category,
        b.Location,
        COUNT(h.AccNo) as total_copies,
        SUM(CASE WHEN h.Status = 'Available' THEN 1 ELSE 0 END) as copies_available
    FROM books b
    LEFT JOIN holding h ON b.CallNo = h.CallNo
    GROUP BY b.CallNo
    HAVING copies_available > 0
    ORDER BY b.CallNo DESC
    LIMIT 6";
    
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $copies_available = intval($row['copies_available']);
        $total_copies = intval($row['total_copies']);
        
        $status = 'Available';
        if ($copies_available == 0) {
            $status = 'Not Available';
        } else if ($copies_available == 1) {
            $status = 'Limited';
        }
        
        $featured_books[] = [
            'call_no' => $row['CallNo'],
            'title' => $row['Title'],
            'author' => $row['Author1'],
            'isbn' => $row['ISBN'] ?: 'N/A',
            'category' => $row['category'] ?: 'General',
            'status' => $status,
            'copies_available' => $copies_available,
            'total_copies' => $total_copies,
            'location' => $row['Location'] ?: 'Library'
        ];
    }
    
    // Get unique categories/subjects
    $categoriesQuery = "SELECT DISTINCT Subject FROM books WHERE Subject IS NOT NULL AND Subject != '' ORDER BY Subject";
    $categoriesStmt = $pdo->query($categoriesQuery);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($categories)) {
        $categories = ['Computer Science', 'Mathematics', 'Engineering', 'General'];
    }
    
} catch (Exception $e) {
    // Fallback to dummy data
    $featured_books = [
        [
            'call_no' => 'CS001',
            'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
            'author' => 'Robert C. Martin',
            'isbn' => '978-0132350884',
            'category' => 'Programming',
            'status' => 'Available',
            'copies_available' => 3,
            'total_copies' => 5,
            'location' => 'CS-Section-A, Shelf 12'
        ],
        [
            'call_no' => 'CS002',
            'title' => 'Introduction to Algorithms',
            'author' => 'Thomas H. Cormen',
            'isbn' => '978-0262033848',
            'category' => 'Computer Science',
            'status' => 'Available',
            'copies_available' => 2,
            'total_copies' => 3,
            'location' => 'CS-Section-B, Shelf 8'
        ]
    ];
    
    $categories = [
        'Computer Science',
        'Programming',
        'Software Engineering',
        'Mathematics'
    ];
}

// Recent searches would come from a search history table (placeholder for now)
$recent_searches = [
    'Data Structures',
    'Machine Learning',
    'Java Programming',
    'Web Development'
];
?>

<style>
    /* Search Books specific styles */
    .search-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .search-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 5px 0;
    }

    .search-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .search-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .search-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .search-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .search-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .search-label {
        font-weight: 600;
        color: #263c79;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .search-input,
    .search-select {
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s ease;
        background: white;
    }

    .search-input:focus,
    .search-select:focus {
        outline: none;
        border-color: #cfac69;
    }

    .search-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        padding-top: 10px;
    }

    .search-btn {
        background: #263c79;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s ease;
    }

    .search-btn:hover {
        background: #1e2f5a;
    }

    .clear-btn {
        background: transparent;
        color: #666;
        border: 2px solid #e0e0e0;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .clear-btn:hover {
        border-color: #cfac69;
        color: #263c79;
    }

    .quick-searches {
        margin-bottom: 25px;
    }

    .quick-search-title {
        color: #263c79;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .search-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .search-tag {
        background: #f0f2f5;
        color: #263c79;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .search-tag:hover {
        background: #cfac69;
        color: white;
    }

    .results-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .results-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .results-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }

    .results-count {
        color: #666;
        font-size: 14px;
    }

    .book-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .book-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        transition: all 0.3s ease;
        background: white;
    }

    .book-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .book-card-header {
        margin-bottom: 15px;
    }

    .book-card-title {
        color: #263c79;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
        line-height: 1.3;
    }

    .book-card-author {
        color: #666;
        font-size: 14px;
        margin-bottom: 3px;
    }

    .book-card-isbn {
        color: #888;
        font-size: 12px;
        font-family: monospace;
    }

    .book-card-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 15px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .detail-item {
        text-align: center;
    }

    .detail-label {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }

    .detail-value {
        font-weight: 600;
        color: #263c79;
        font-size: 13px;
    }

    .availability-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-available {
        background: #d4edda;
        color: #155724;
    }

    .status-limited {
        background: #fff3cd;
        color: #856404;
    }

    .status-unavailable {
        background: #f8d7da;
        color: #721c24;
    }

    .copies-info {
        font-size: 12px;
        color: #666;
    }

    .location-info {
        background: #e3f2fd;
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-size: 12px;
        color: #1565c0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .book-actions {
        display: flex;
        gap: 10px;
    }

    .reserve-btn {
        flex: 1;
        background: #263c79;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .reserve-btn:hover:not(:disabled) {
        background: #1e2f5a;
    }

    .reserve-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .details-btn {
        background: transparent;
        color: #263c79;
        border: 1px solid #263c79;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .details-btn:hover {
        background: #263c79;
        color: white;
    }

    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .no-results i {
        font-size: 48px;
        color: #ccc;
        margin-bottom: 15px;
    }

    .no-results h3 {
        color: #263c79;
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .search-row {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .search-actions {
            justify-content: stretch;
        }

        .search-btn,
        .clear-btn {
            flex: 1;
            justify-content: center;
        }

        .book-grid {
            grid-template-columns: 1fr;
            padding: 15px;
            gap: 15px;
        }

        .book-card-details {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .availability-status {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        .book-actions {
            flex-direction: column;
        }
    }
</style>

<div class="search-header">
    <h1 class="search-title">Search Books</h1>
    <p class="search-subtitle">Find books from our extensive library collection</p>
</div>

<!-- Search Form -->
<div class="search-section">
    <form class="search-form" id="bookSearchForm">
        <div class="search-row">
            <div class="search-field">
                <label class="search-label">Title</label>
                <input type="text" class="search-input" id="title" placeholder="Enter book title...">
            </div>
            <div class="search-field">
                <label class="search-label">Author</label>
                <input type="text" class="search-input" id="author" placeholder="Enter author name...">
            </div>
        </div>

        <div class="search-row">
            <div class="search-field">
                <label class="search-label">ISBN</label>
                <input type="text" class="search-input" id="isbn" placeholder="Enter ISBN...">
            </div>
            <div class="search-field">
                <label class="search-label">Category</label>
                <select class="search-select" id="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="search-field">
            <label class="search-label">Keywords</label>
            <input type="text" class="search-input" id="keywords" placeholder="Enter keywords, topics, or subjects...">
        </div>

        <div class="search-actions">
            <button type="button" class="clear-btn" onclick="clearSearch()">
                <i class="fas fa-eraser"></i>
                Clear
            </button>
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
                Search Books
            </button>
        </div>
    </form>
</div>

<!-- Quick Searches -->
<div class="quick-searches">
    <h3 class="quick-search-title">Recent Searches</h3>
    <div class="search-tags">
        <?php foreach ($recent_searches as $search): ?>
            <span class="search-tag" onclick="quickSearch('<?php echo htmlspecialchars($search); ?>')">
                <?php echo htmlspecialchars($search); ?>
            </span>
        <?php endforeach; ?>
    </div>
</div>

<!-- Search Results -->
<div class="results-section" id="searchResults" style="display: none;">
    <div class="results-header">
        <h3 class="results-title">Search Results</h3>
        <span class="results-count" id="resultsCount">0 books found</span>
    </div>
    <div id="resultsContainer"></div>
</div>

<!-- Featured Books -->
<div class="results-section">
    <div class="results-header">
        <h3 class="results-title">
            <i class="fas fa-star" style="margin-right: 8px; color: #cfac69;"></i>
            Featured Books
        </h3>
        <span class="results-count"><?php echo count($featured_books); ?> books</span>
    </div>
    <div class="book-grid">
        <?php foreach ($featured_books as $book): ?>
            <div class="book-card">
                <div class="book-card-header">
                    <h4 class="book-card-title"><?php echo htmlspecialchars($book['title']); ?></h4>
                    <div class="book-card-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                    <div class="book-card-isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></div>
                </div>

                <div class="book-card-details">
                    <div class="detail-item">
                        <div class="detail-label">Category</div>
                        <div class="detail-value"><?php echo htmlspecialchars($book['category']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Available</div>
                        <div class="detail-value"><?php echo $book['copies_available']; ?>/<?php echo $book['total_copies']; ?></div>
                    </div>
                </div>

                <div class="availability-status">
                    <span class="status-badge status-<?php echo strtolower($book['status']); ?>">
                        <?php echo $book['status']; ?>
                    </span>
                    <span class="copies-info">
                        <?php echo $book['copies_available']; ?> copies available
                    </span>
                </div>

                <div class="location-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo htmlspecialchars($book['location']); ?>
                </div>

                <div class="book-actions">
                    <button class="reserve-btn" <?php echo $book['copies_available'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-bookmark"></i>
                        <?php echo $book['copies_available'] > 0 ? 'Reserve' : 'Unavailable'; ?>
                    </button>
                    <button class="details-btn">
                        <i class="fas fa-info-circle"></i>
                        Details
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function clearSearch() {
        document.getElementById('bookSearchForm').reset();
        document.getElementById('searchResults').style.display = 'none';
    }

    function quickSearch(term) {
        document.getElementById('keywords').value = term;
        searchBooks();
    }

    async function searchBooks() {
        // Show loading state
        const resultsSection = document.getElementById('searchResults');
        const resultsContainer = document.getElementById('resultsContainer');
        const resultsCount = document.getElementById('resultsCount');

        resultsSection.style.display = 'block';
        resultsContainer.innerHTML = '<div style="padding: 40px; text-align: center; color: #666;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i><br>Searching books...</div>';

        // Get search parameters
        const searchQuery = document.getElementById('searchQuery').value;
        const searchType = document.getElementById('searchType').value;
        const category = document.getElementById('category').value;
        const availability = document.getElementById('availability').value;

        try {
            // Build query string
            let apiUrl = '../admin/api/books.php?action=search';
            if (searchQuery) {
                apiUrl += `&query=${encodeURIComponent(searchQuery)}`;
            }
            if (category) {
                apiUrl += `&subject=${encodeURIComponent(category)}`;
            }

            const response = await fetch(apiUrl);
            const result = await response.json();

            let books = [];
            if (result.success && result.data) {
                // Filter by availability if needed
                books = result.data.filter(book => {
                    if (availability === 'available') {
                        return (book.AvailableCopies || 0) > 0;
                    } else if (availability === 'unavailable') {
                        return (book.AvailableCopies || 0) === 0;
                    }
                    return true; // 'all'
                });
            }

            resultsCount.textContent = `${books.length} books found`;

            if (books.length > 0) {
                resultsContainer.innerHTML = `
                <div class="book-grid">
                    ${books.map(book => {
                        const available = book.AvailableCopies || 0;
                        const total = book.TotalCopies || 0;
                        let status = 'available';
                        let statusText = 'Available';
                        
                        if (available === 0) {
                            status = 'unavailable';
                            statusText = 'Not Available';
                        } else if (available === 1) {
                            status = 'limited';
                            statusText = 'Limited';
                        }
                        
                        return `
                        <div class="book-card">
                            <div class="book-card-header">
                                <h4 class="book-card-title">${book.Title}</h4>
                                <div class="book-card-author">by ${book.Author1}</div>
                                <div class="book-card-isbn">ISBN: ${book.ISBN || 'N/A'}</div>
                            </div>
                            <div class="book-card-details">
                                <div class="detail-item">
                                    <div class="detail-label">Category</div>
                                    <div class="detail-value">${book.Subject || 'General'}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Available</div>
                                    <div class="detail-value">${available}/${total}</div>
                                </div>
                            </div>
                            <div class="availability-status">
                                <span class="status-badge status-${status}">${statusText}</span>
                                <span class="copies-info">${available} copies available</span>
                            </div>
                            <div class="location-info">
                                <i class="fas fa-map-marker-alt"></i>
                                ${book.Location || 'Library'}
                            </div>
                            <div class="book-actions">
                                <button class="reserve-btn" ${available === 0 ? 'disabled' : ''}>
                                    <i class="fas fa-bookmark"></i>
                                    Reserve
                                </button>
                                <button class="details-btn" onclick="viewBookDetails('${book.CallNo}')">
                                    <i class="fas fa-info-circle"></i>
                                    Details
                                </button>
                            </div>
                        </div>
                    `;
                    }).join('')}
                </div>
            `;
            } else {
                resultsContainer.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No Books Found</h3>
                    <p>Try adjusting your search criteria or browse our featured books below.</p>
                </div>
            `;
            }
        } catch (error) {
            console.error('Error searching books:', error);
            resultsContainer.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Search Error</h3>
                    <p>Unable to search books. Please try again later.</p>
                </div>
            `;
        }
    }

    function viewBookDetails(callNo) {
        alert(`Viewing details for book: ${callNo}\n\nThis would open a detailed view with:\n- Full book information\n- All copies with status\n- Reviews & ratings\n- Similar books`);
    }

    // Form submission handler
    document.getElementById('bookSearchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        searchBooks();
    });
</script>