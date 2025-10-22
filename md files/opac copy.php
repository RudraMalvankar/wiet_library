<?php
// OPAC - Online Public Access Catalog
// Public search interface for library books - no login required

// Mock data for demonstration - replace with actual database queries
$categories = [
    'Computer Science',
    'Programming',
    'Software Engineering',
    'Data Science',
    'Artificial Intelligence',
    'Cybersecurity',
    'Web Development',
    'Mobile Development',
    'Mathematics',
    'Physics',
    'Chemistry',
    'Biology',
    'Engineering',
    'Literature'
];

$featured_books = [
    [
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
        'title' => 'Introduction to Algorithms',
        'author' => 'Thomas H. Cormen',
        'isbn' => '978-0262033848',
        'category' => 'Computer Science',
        'status' => 'Available',
        'copies_available' => 2,
        'total_copies' => 3,
        'location' => 'CS-Section-B, Shelf 8'
    ],
    [
        'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
        'author' => 'Gang of Four',
        'isbn' => '978-0201633610',
        'category' => 'Software Engineering',
        'status' => 'Limited',
        'copies_available' => 1,
        'total_copies' => 2,
        'location' => 'CS-Section-C, Shelf 15'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OPAC - WIET Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Lato:wght@300;400;700&display=swap"
        rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Typography Setup */
    body {
        font-family: "Lato", sans-serif;
        font-weight: 400;
        font-style: normal;
        background: white;
        min-height: 100vh;
        padding: 1rem;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    /* Headings use Poppins font */
    h1, h2, h3, h4, h5, h6 {
        font-family: "Poppins", sans-serif;
        font-weight: 700;
    }



    .search-container {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
        padding: 1.5rem;
        border-radius: 20px;
        border: 1px solid #e9ecef;
        box-shadow: 0 10px 40px rgba(38, 60, 121, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 900px;
        position: relative;
        overflow: hidden;
        margin-bottom: 25px;
    }

    /* Watermark */
    .search-watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
        pointer-events: none;
        width: 120px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search-watermark img {
        width: 120px !important;
        height: 80px !important;
        opacity: 0.15;
    }

    .search-header {
        text-align: center;
        margin-bottom: 20px;
        z-index: 1;
        position: relative;
    }

    .logo {
        width: 70px;
        height: 70px;
        margin: 0 auto 10px;
        border-radius: 50%;
        background: linear-gradient(135deg, #263c79 0%, #4a68a8 100%);
        border: 3px solid #263c79;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
    }

    .search-title {
        color: #263c79;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .search-subtitle {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .search-description {
        color: #888;
        font-size: 0.8rem;
        font-style: italic;
    }

    .search-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
        z-index: 2;
        position: relative;
    }

    .search-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .search-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .search-label {
        font-weight: 600;
        color: #263c79;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 2px;
        font-family: 'Poppins', sans-serif;
    }

    .search-input,
    .search-select {
        padding: 14px 18px;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 400;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        color: #495057;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }

    .search-input:focus,
    .search-select:focus {
        outline: none;
        border-color: #263c79;
        background: white;
        box-shadow: 0 0 0 3px rgba(38, 60, 121, 0.1), 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .search-input:hover:not(:focus),
    .search-select:hover:not(:focus) {
        border-color: #adb5bd;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
    }

    .search-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        padding-top: 8px;
        margin-top: 5px;
    }

    .search-btn {
        background: linear-gradient(135deg, #263c79 0%, #1e2d5f 100%);
        color: white;
        border: none;
        padding: 16px 32px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(38, 60, 121, 0.3);
        font-family: 'Poppins', sans-serif;
        letter-spacing: 0.5px;
    }

    .search-btn:hover {
        background: linear-gradient(135deg, #1e2d5f 0%, #152247 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(38, 60, 121, 0.4);
    }

    .search-btn:active {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(38, 60, 121, 0.3);
    }

    .clear-btn {
        background: white;
        color: #6c757d;
        border: 2px solid #dee2e6;
        padding: 16px 28px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Poppins', sans-serif;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .clear-btn:hover {
        border-color: #263c79;
        color: #263c79;
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .clear-btn:active {
        transform: translateY(0px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .back-btn {
        background: linear-gradient(135deg, #cfac69 0%, #d4b574 100%);
        color: white;
        border: none;
        padding: 16px 28px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Poppins', sans-serif;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(207, 172, 105, 0.3);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .back-btn:hover {
        background: linear-gradient(135deg, #b8954a 0%, #c49f5c 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(207, 172, 105, 0.4);
    }

    .back-btn:active {
        transform: translateY(0px);
        box-shadow: 0 4px 12px rgba(207, 172, 105, 0.3);
    }

    /* Results Section */
    .results-section {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #cfac69;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(38, 60, 121, 0.1);
        width: 100%;
        max-width: 1200px;
        margin-bottom: 40px;
        display: none;
        position: relative;
    }

    .results-section.show {
        display: block;
    }

    .results-header {
        background: #f8f9fa;
        padding: 12px 20px;
        border-bottom: 2px solid #263c79;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }



    .results-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        font-family: 'Poppins', sans-serif;
        letter-spacing: 0.5px;
    }

    .results-count {
        color: #666;
        font-size: 14px;
        font-weight: 500;
        background: rgba(255, 255, 255, 0.9);
        padding: 6px 12px;
        border-radius: 15px;
        border: 1px solid rgba(38, 60, 121, 0.2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .book-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        padding: 25px;
        background: linear-gradient(135deg, rgba(207, 172, 105, 0.03) 0%, rgba(38, 60, 121, 0.02) 100%);
    }

    .book-card {
        border: 2px solid #cfac69;
        border-radius: 16px;
        padding: 15px;
        background: linear-gradient(145deg, #ffffff 0%, #fefefe 100%);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .book-card-header {
        margin-bottom: 12px;
        position: relative;
        z-index: 2;
    }

    .book-card-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
        line-height: 1.4;
        font-family: 'Poppins', sans-serif;
        letter-spacing: 0.3px;
    }

    .book-card-author {
        color: #6c757d;
        font-size: 15px;
        font-weight: 500;
        margin-bottom: 4px;
        font-style: italic;
        margin-bottom: 3px;
    }

    .book-card-isbn {
        color: #888;
        font-size: 12px;
        font-family: monospace;
    }

    .book-card-details {
        display: inline-block;
        min-width: 0;
        width: auto;
        padding: 8px 16px;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #cfac69 0%, #d4b574 100%);
        border-radius: 8px;
        border: none;
        box-sizing: border-box;
        text-align: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .detail-item {
        text-align: center;
    }

    .detail-label {
        font-size: 10px;
        color: #263c79;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 4px;
        font-weight: 600;
        opacity: 0.8;
    }

    .detail-value {
        font-weight: 700;
        color: #263c79;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
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
        border: 1px solid #bbdefb;
    }

    .login-prompt {
        background: linear-gradient(135deg, rgba(207, 172, 105, 0.1) 0%, rgba(207, 172, 105, 0.05) 100%);
        color: #263c79;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 12px;
        text-align: center;
        border: 2px solid #cfac69;
        margin-top: 10px;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }

    .login-prompt a {
        color: #263c79;
        text-decoration: none;
        font-weight: 700;
        position: relative;
        z-index: 2;
    }

    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .no-results i {
        font-size: 42px;
        margin-bottom: 18px;
        color: #263c79;
    }

    .no-results h3 {
        color: #263c79;
        margin-bottom: 10px;
        font-size: 24px;
    }

    .no-results p {
        font-size: 16px;
        color: #666;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        body {
            padding: 0.5rem;
        }

        .search-container {
            padding: 1rem;
            margin-bottom: 20px;
            border-radius: 15px;
        }

        .search-title {
            font-size: 1.4rem;
        }

        .search-subtitle {
            font-size: 0.8rem;
        }

        .search-description {
            font-size: 0.75rem;
        }

        .search-row {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .search-actions {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .search-btn, .clear-btn, .back-btn {
            width: 100%;
            justify-content: center;
            padding: 14px 24px;
        }

        .book-grid {
            grid-template-columns: 1fr;
            gap: 15px;
            padding: 15px;
        }

        .book-card {
            padding: 12px;
            border-radius: 12px;
        }

        .book-card-title {
            font-size: 16px;
            line-height: 1.3;
        }

        .book-card-author {
            font-size: 14px;
        }

        .book-card-isbn {
            font-size: 11px;
        }

        .results-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            padding: 10px 15px;
        }

        .results-title {
            font-size: 16px;
        }

        .results-count {
            font-size: 12px;
            padding: 4px 8px;
        }

        .search-input,
        .search-select {
            padding: 12px 14px;
            font-size: 13px;
        }

        .search-label {
            font-size: 12px;
        }

        .detail-item {
            margin-bottom: 8px;
        }

        .detail-label {
            font-size: 9px;
        }

        .detail-value {
            font-size: 13px;
        }

        .location-info {
            font-size: 11px;
            padding: 6px 8px;
        }

        .login-prompt {
            font-size: 11px;
            padding: 8px 10px;
        }
    }

    @media (max-width: 480px) {
        body {
            padding: 0.25rem;
        }

        .search-container {
            padding: 1rem;
            margin-bottom: 20px;
            border-radius: 12px;
        }

        .logo {
            width: 55px;
            height: 55px;
            font-size: 1.4rem;
        }

        .search-title {
            font-size: 1.2rem;
        }

        .search-subtitle {
            font-size: 0.75rem;
        }

        .search-description {
            font-size: 0.7rem;
        }

        .search-form {
            gap: 18px;
        }

        .search-field {
            gap: 6px;
        }

        .search-input,
        .search-select {
            padding: 10px 12px;
            font-size: 12px;
            border-radius: 8px;
        }

        .search-label {
            font-size: 11px;
        }

        .search-btn, .clear-btn, .back-btn {
            padding: 12px 20px;
            font-size: 13px;
            border-radius: 8px;
        }

        .book-grid {
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 12px;
        }

        .book-card {
            padding: 10px;
            border-radius: 10px;
        }

        .book-card-title {
            font-size: 15px;
            margin-bottom: 6px;
        }

        .book-card-author {
            font-size: 13px;
        }

        .book-card-isbn {
            font-size: 10px;
        }

        .results-header {
            padding: 8px 12px;
        }

        .results-title {
            font-size: 14px;
        }

        .results-count {
            font-size: 11px;
            padding: 3px 6px;
        }

        .book-card-details {
            padding: 6px 12px;
            margin-bottom: 8px;
        }

        .detail-label {
            font-size: 8px;
            margin-bottom: 2px;
        }

        .detail-value {
            font-size: 12px;
        }

        .status-badge {
            font-size: 10px;
            padding: 3px 6px;
        }

        .copies-info {
            font-size: 11px;
        }

        .location-info {
            font-size: 10px;
            padding: 5px 6px;
        }

        .login-prompt {
            font-size: 10px;
            padding: 6px 8px;
        }

        .availability-status {
            margin-bottom: 10px;
        }
    }

    @media (max-width: 360px) {
        .search-container {
            padding: 0.8rem;
        }

        .search-title {
            font-size: 1.1rem;
        }

        .logo {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }

        .book-card-title {
            font-size: 14px;
        }

        .search-input,
        .search-select {
            padding: 8px 10px;
            font-size: 11px;
        }
    }
</style>

<body>
    <!-- Search Form Container -->
    <div class="search-container">
            <div class="search-watermark">
                <img src="images/watumull%20logo.png" alt="Watumull Logo">
            </div>
            
            <div class="search-header">
                <div class="logo">
                    <i class="fas fa-search"></i>
                </div>
                <h1 class="search-title">OPAC</h1>
                <p class="search-subtitle">Online Public Access Catalog</p>
                <p class="search-description">Search our library collection • No login required</p>
            </div>

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
                    <a href="wiet_lib/index.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back to Home
                    </a>
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

        <!-- Search Results -->
        <div class="results-section" id="searchResults">
            <div class="results-header">
                <h3 class="results-title">Search Results</h3>
                <span class="results-count" id="resultsCount">0 books found</span>
            </div>
            <div id="resultsContainer"></div>
        </div>

    <script>
        function clearSearch() {
            document.getElementById('bookSearchForm').reset();
            document.getElementById('searchResults').classList.remove('show');
        }

        function searchBooks() {
            // Get form values
            const title = document.getElementById('title').value.trim();
            const author = document.getElementById('author').value.trim();
            const isbn = document.getElementById('isbn').value.trim();
            const category = document.getElementById('category').value;
            const keywords = document.getElementById('keywords').value.trim();

            // Check if at least one field is filled
            if (!title && !author && !isbn && !category && !keywords) {
                alert('Please enter at least one search criteria.');
                return;
            }

            // Show loading state
            const resultsSection = document.getElementById('searchResults');
            const resultsContainer = document.getElementById('resultsContainer');
            const resultsCount = document.getElementById('resultsCount');

            resultsSection.classList.add('show');
            resultsContainer.innerHTML = `
                <div style="padding: 60px; text-align: center; color: #666;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 28px; margin-bottom: 15px; color: #263c79;"></i>
                    <br><strong>Searching our library collection...</strong>
                    <br><small>Please wait while we find books for you</small>
                </div>
            `;

            // Smooth scroll to results section with a slight delay to ensure it's visible
            setTimeout(() => {
                resultsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                    inline: 'nearest'
                });
            }, 100);

            // Simulate search - replace with actual API call
            setTimeout(() => {
                // Mock search results based on form input
                const mockResults = [];
                
                // Add some mock results based on search terms
                if (title.toLowerCase().includes('algorithm') || keywords.toLowerCase().includes('algorithm')) {
                    mockResults.push({
                        title: 'Introduction to Algorithms',
                        author: 'Thomas H. Cormen',
                        isbn: '978-0262033848',
                        category: 'Computer Science',
                        status: 'Available',
                        copies_available: 2,
                        total_copies: 3,
                        location: 'CS-Section-B, Shelf 8'
                    });
                }

                if (title.toLowerCase().includes('code') || keywords.toLowerCase().includes('programming')) {
                    mockResults.push({
                        title: 'Clean Code: A Handbook of Agile Software Craftsmanship',
                        author: 'Robert C. Martin',
                        isbn: '978-0132350884',
                        category: 'Programming',
                        status: 'Available',
                        copies_available: 3,
                        total_copies: 5,
                        location: 'CS-Section-A, Shelf 12'
                    });
                }

                if (category === 'Software Engineering' || keywords.toLowerCase().includes('pattern')) {
                    mockResults.push({
                        title: 'Design Patterns: Elements of Reusable Object-Oriented Software',
                        author: 'Gang of Four',
                        isbn: '978-0201633610',
                        category: 'Software Engineering',
                        status: 'Limited',
                        copies_available: 1,
                        total_copies: 2,
                        location: 'CS-Section-C, Shelf 15'
                    });
                }

                // If no specific matches, add a generic result
                if (mockResults.length === 0 && (title || author || isbn || keywords)) {
                    mockResults.push({
                        title: 'Advanced Data Structures and Algorithms',
                        author: 'Peter Brass',
                        isbn: '978-0521880374',
                        category: 'Computer Science',
                        status: 'Available',
                        copies_available: 2,
                        total_copies: 3,
                        location: 'CS-Section-A, Shelf 10'
                    });
                }

                resultsCount.textContent = `${mockResults.length} books found`;

                if (mockResults.length > 0) {
                    resultsContainer.innerHTML = `
                    <div class="book-grid">
                        ${mockResults.map(book => `
                            <div class="book-card">
                                <div class="book-card-header">
                                    <h4 class="book-card-title">${book.title}</h4>
                                    <div class="book-card-author">by ${book.author}</div>
                                    <div class="book-card-isbn">ISBN: ${book.isbn}</div>
                                </div>
                                <div class="book-card-details">
                                    <div class="detail-item">
                                        <div class="detail-label">Category</div>
                                        <div class="detail-value">${book.category}</div>
                                    </div>
                                </div>
                                <div class="login-prompt">
                                    <i class="fas fa-info-circle"></i>
                                    <a href="student_login.php">Login as Student</a> to reserve this book
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
                } else {
                    resultsContainer.innerHTML = `
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No Books Found</h3>
                        <p>We couldn't find any books matching your search criteria.</p>
                        <p>Try adjusting your search terms or browse our featured books below.</p>
                    </div>
                `;
                }
            }, 1500);
        }

        // Form submission handler
        document.getElementById('bookSearchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            searchBooks();
        });

        // Auto-focus on first input
        document.getElementById('title').focus();
    </script>
</body>

</html>
