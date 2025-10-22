<?php
// Recommendations Content - Personalized book recommendations for students
// This file will be included in the main content area

// Session variables for student info
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'John Doe';
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 'STU2024001';

// Mock data for demonstration - replace with actual database queries
$recommended_books = [
    [
        'id' => 'REC001',
        'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
        'author' => 'Gang of Four',
        'isbn' => '978-0201633610',
        'category' => 'Software Engineering',
        'rating' => 4.8,
        'availability' => 'Available',
        'copies_available' => 3,
        'total_copies' => 5,
        'reason' => 'Based on your interest in Clean Code',
        'recommendation_score' => 95,
        'cover_image' => 'design_patterns.jpg',
        'description' => 'A foundational text on software design patterns that every programmer should read.',
        'tags' => ['Programming', 'Design', 'Object-Oriented'],
        'difficulty' => 'Intermediate',
        'pages' => 395
    ],
    [
        'id' => 'REC002',
        'title' => 'Introduction to Machine Learning',
        'author' => 'Alpaydin Ethem',
        'isbn' => '978-0262028189',
        'category' => 'Computer Science',
        'rating' => 4.6,
        'availability' => 'Available',
        'copies_available' => 2,
        'total_copies' => 4,
        'reason' => 'Popular among CSE 3rd year students',
        'recommendation_score' => 88,
        'cover_image' => 'ml_intro.jpg',
        'description' => 'Comprehensive introduction to machine learning concepts and algorithms.',
        'tags' => ['Machine Learning', 'AI', 'Algorithms'],
        'difficulty' => 'Advanced',
        'pages' => 640
    ],
    [
        'id' => 'REC003',
        'title' => 'Computer Networks: A Top-Down Approach',
        'author' => 'James Kurose, Keith Ross',
        'isbn' => '978-0133594140',
        'category' => 'Networking',
        'rating' => 4.7,
        'availability' => 'Reserved',
        'copies_available' => 0,
        'total_copies' => 3,
        'reason' => 'Recommended for your semester',
        'recommendation_score' => 92,
        'cover_image' => 'networks.jpg',
        'description' => 'The definitive guide to computer networking concepts and protocols.',
        'tags' => ['Networking', 'Protocols', 'Internet'],
        'difficulty' => 'Intermediate',
        'pages' => 864
    ],
    [
        'id' => 'REC004',
        'title' => 'Artificial Intelligence: A Modern Approach',
        'author' => 'Stuart Russell, Peter Norvig',
        'isbn' => '978-0134610993',
        'category' => 'Artificial Intelligence',
        'rating' => 4.9,
        'availability' => 'Available',
        'copies_available' => 1,
        'total_copies' => 2,
        'reason' => 'Trending in your field',
        'recommendation_score' => 96,
        'cover_image' => 'ai_modern.jpg',
        'description' => 'The most comprehensive and authoritative introduction to AI.',
        'tags' => ['AI', 'Machine Learning', 'Robotics'],
        'difficulty' => 'Advanced',
        'pages' => 1136
    ],
    [
        'id' => 'REC005',
        'title' => 'Operating System Concepts',
        'author' => 'Abraham Silberschatz',
        'isbn' => '978-1118063330',
        'category' => 'Operating Systems',
        'rating' => 4.5,
        'availability' => 'Available',
        'copies_available' => 4,
        'total_copies' => 6,
        'reason' => 'Course curriculum match',
        'recommendation_score' => 85,
        'cover_image' => 'os_concepts.jpg',
        'description' => 'Essential concepts in operating system design and implementation.',
        'tags' => ['Operating Systems', 'System Programming'],
        'difficulty' => 'Intermediate',
        'pages' => 976
    ],
    [
        'id' => 'REC006',
        'title' => 'The Pragmatic Programmer',
        'author' => 'David Thomas, Andrew Hunt',
        'isbn' => '978-0201616224',
        'category' => 'Software Development',
        'rating' => 4.8,
        'availability' => 'Available',
        'copies_available' => 2,
        'total_copies' => 3,
        'reason' => 'Highly rated by peers',
        'recommendation_score' => 90,
        'cover_image' => 'pragmatic_programmer.jpg',
        'description' => 'Your journey to mastery in software development.',
        'tags' => ['Programming', 'Best Practices', 'Career'],
        'difficulty' => 'Beginner',
        'pages' => 352
    ]
];

$recommendation_categories = [
    'all' => 'All Recommendations',
    'course_based' => 'Course Related',
    'interest_based' => 'Based on Interests',
    'peer_popular' => 'Popular Among Peers',
    'trending' => 'Trending Now',
    'new_arrivals' => 'New Arrivals'
];

$reading_preferences = [
    'difficulty_level' => 'Intermediate',
    'preferred_categories' => ['Computer Science', 'Software Engineering', 'AI'],
    'reading_goal' => 2, // books per month
    'current_streak' => 5, // days
    'total_books_read' => 23
];

$personalized_stats = [
    'books_recommended' => count($recommended_books),
    'success_rate' => 78, // percentage of recommended books that were borrowed
    'categories_covered' => 6,
    'avg_rating' => 4.7
];
?>

<style>
    .recommendations-header {
        margin-top: 0;
        margin-bottom: 25px;
        padding-top: 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .recommendations-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .recommendations-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .recommendations-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 25px;
        margin-bottom: 30px;
    }

    .recommendations-main-content {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .sidebar-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        color: #263c79;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-controls {
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .filter-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }

    .filter-tab {
        background: transparent;
        border: 1px solid #e0e0e0;
        color: #666;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-tab.active {
        background: #263c79;
        color: white;
        border-color: #263c79;
    }

    .filter-tab:hover {
        border-color: #cfac69;
    }

    .sort-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sort-label {
        font-size: 13px;
        color: #666;
        font-weight: 500;
    }

    .sort-select {
        padding: 6px 10px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 12px;
        color: #263c79;
    }

    .recommendations-grid {
        padding: 20px;
        display: grid;
        gap: 20px;
    }

    .book-card {
        display: flex;
        gap: 15px;
        padding: 20px;
        border: 1px solid #f0f0f0;
        border-radius: 6px;
        background: #fafafa;
        transition: all 0.3s ease;
    }

    .book-card:hover {
        border-color: #cfac69;
        box-shadow: 0 4px 12px rgba(207, 172, 105, 0.15);
        transform: translateY(-2px);
    }

    .book-cover {
        width: 80px;
        height: 100px;
        background: linear-gradient(135deg, #263c79, #cfac69);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 12px;
        text-align: center;
        line-height: 1.2;
        flex-shrink: 0;
    }

    .book-details {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .book-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .book-title {
        font-size: 16px;
        font-weight: 600;
        color: #263c79;
        line-height: 1.3;
        margin-bottom: 3px;
    }

    .book-author {
        font-size: 13px;
        color: #666;
        margin-bottom: 8px;
    }

    .recommendation-badge {
        background: #e8f5e8;
        color: #2e7d32;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .book-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .rating {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stars {
        color: #ffc107;
        font-size: 12px;
    }

    .rating-value {
        font-size: 12px;
        color: #666;
        font-weight: 500;
    }

    .availability {
        font-size: 11px;
        padding: 3px 6px;
        border-radius: 3px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .availability.available {
        background: #d4edda;
        color: #155724;
    }

    .availability.reserved {
        background: #fff3cd;
        color: #856404;
    }

    .availability.unavailable {
        background: #f8d7da;
        color: #721c24;
    }

    .difficulty {
        font-size: 11px;
        padding: 3px 6px;
        border-radius: 3px;
        background: #f0f2f5;
        color: #263c79;
        font-weight: 500;
    }

    .book-description {
        font-size: 13px;
        color: #666;
        line-height: 1.4;
        margin-bottom: 10px;
    }

    .book-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-bottom: 12px;
    }

    .tag {
        background: #e3f2fd;
        color: #1976d2;
        padding: 2px 6px;
        border-radius: 2px;
        font-size: 10px;
        font-weight: 500;
    }

    .book-actions {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }

    .action-btn {
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-primary {
        background: #263c79;
        color: white;
    }

    .btn-primary:hover {
        background: #1e2f5a;
    }

    .btn-secondary {
        background: transparent;
        color: #263c79;
        border: 1px solid #263c79;
    }

    .btn-secondary:hover {
        background: #263c79;
        color: white;
    }

    .btn-disabled {
        background: #e0e0e0;
        color: #999;
        cursor: not-allowed;
    }

    .recommendation-reason {
        background: #f8f9fa;
        border-left: 3px solid #cfac69;
        padding: 8px 12px;
        font-size: 12px;
        color: #263c79;
        font-weight: 500;
        margin-bottom: 10px;
        border-radius: 0 4px 4px 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 15px;
    }

    .stat-item {
        text-align: center;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .stat-value {
        font-size: 18px;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 3px;
    }

    .stat-label {
        font-size: 10px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .preferences-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .preference-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13px;
    }

    .preference-item:last-child {
        border-bottom: none;
    }

    .preference-label {
        color: #666;
    }

    .preference-value {
        color: #263c79;
        font-weight: 500;
    }

    .reading-progress {
        margin-top: 15px;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #263c79, #cfac69);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .progress-text {
        font-size: 11px;
        color: #666;
        margin-top: 5px;
        text-align: center;
    }

    @media (max-width: 968px) {
        .recommendations-layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            order: -1;
        }

        .book-card {
            flex-direction: column;
            text-align: center;
        }

        .book-cover {
            width: 100px;
            height: 120px;
            margin: 0 auto 15px;
        }

        .book-header {
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
    }

    @media (max-width: 768px) {
        .filter-tabs {
            flex-direction: column;
        }

        .filter-tab {
            text-align: center;
        }

        .book-meta {
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="recommendations-header">
    <h1 class="recommendations-title">Recommendations</h1>
    <p class="recommendations-subtitle">Discover books tailored to your interests and academic needs</p>
</div>

<div class="recommendations-layout">
    <!-- Main Content -->
    <div class="recommendations-main-content">
        <!-- Filter Controls -->
        <div class="filter-controls">
            <div class="filter-tabs">
                <?php foreach ($recommendation_categories as $key => $label): ?>
                    <button class="filter-tab <?php echo $key === 'all' ? 'active' : ''; ?>"
                        onclick="filterRecommendations('<?php echo $key; ?>')"
                        data-category="<?php echo $key; ?>">
                        <?php echo $label; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="sort-controls">
                <label class="sort-label">Sort by:</label>
                <select class="sort-select" onchange="sortRecommendations(this.value)">
                    <option value="score">Recommendation Score</option>
                    <option value="rating">Rating</option>
                    <option value="availability">Availability</option>
                    <option value="title">Title A-Z</option>
                </select>
            </div>
        </div>

        <!-- Recommendations Grid -->
        <div class="recommendations-grid" id="recommendations-container">
            <?php foreach ($recommended_books as $book): ?>
                <div class="book-card" data-category="all" data-score="<?php echo $book['recommendation_score']; ?>" data-rating="<?php echo $book['rating']; ?>">
                    <div class="book-cover">
                        <?php echo strtoupper(substr($book['title'], 0, 3)); ?>
                    </div>

                    <div class="book-details">
                        <div class="recommendation-reason">
                            <i class="fas fa-lightbulb"></i> <?php echo htmlspecialchars($book['reason']); ?>
                        </div>

                        <div class="book-header">
                            <div>
                                <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                            </div>
                            <span class="recommendation-badge"><?php echo $book['recommendation_score']; ?>% Match</span>
                        </div>

                        <div class="book-meta">
                            <div class="rating">
                                <span class="stars">
                                    <?php
                                    $rating = $book['rating'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= floor($rating)) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i <= ceil($rating)) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </span>
                                <span class="rating-value"><?php echo $book['rating']; ?></span>
                            </div>

                            <span class="availability <?php echo strtolower(str_replace(' ', '', $book['availability'])); ?>">
                                <?php echo $book['availability']; ?>
                                <?php if ($book['availability'] === 'Available'): ?>
                                    (<?php echo $book['copies_available']; ?>/<?php echo $book['total_copies']; ?>)
                                <?php endif; ?>
                            </span>

                            <span class="difficulty"><?php echo $book['difficulty']; ?></span>
                            <span class="difficulty"><?php echo $book['pages']; ?> pages</span>
                        </div>

                        <p class="book-description"><?php echo htmlspecialchars($book['description']); ?></p>

                        <div class="book-tags">
                            <?php foreach ($book['tags'] as $tag): ?>
                                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="book-actions">
                            <?php if ($book['availability'] === 'Available'): ?>
                                <button class="action-btn btn-primary" onclick="borrowBook('<?php echo $book['id']; ?>')">
                                    <i class="fas fa-plus"></i> Borrow Now
                                </button>
                                <button class="action-btn btn-secondary" onclick="reserveBook('<?php echo $book['id']; ?>')">
                                    <i class="fas fa-bookmark"></i> Reserve
                                </button>
                            <?php elseif ($book['availability'] === 'Reserved'): ?>
                                <button class="action-btn btn-secondary" onclick="joinWaitlist('<?php echo $book['id']; ?>')">
                                    <i class="fas fa-clock"></i> Join Waitlist
                                </button>
                            <?php else: ?>
                                <button class="action-btn btn-disabled" disabled>
                                    <i class="fas fa-times"></i> Unavailable
                                </button>
                            <?php endif; ?>

                            <button class="action-btn btn-secondary" onclick="viewDetails('<?php echo $book['id']; ?>')">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Recommendation Stats -->
        <div class="sidebar-card">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i>
                Your Stats
            </h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $personalized_stats['books_recommended']; ?></div>
                    <div class="stat-label">Recommended</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $personalized_stats['success_rate']; ?>%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $personalized_stats['categories_covered']; ?></div>
                    <div class="stat-label">Categories</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $personalized_stats['avg_rating']; ?></div>
                    <div class="stat-label">Avg Rating</div>
                </div>
            </div>
        </div>

        <!-- Reading Preferences -->
        <div class="sidebar-card">
            <h3 class="card-title">
                <i class="fas fa-user-cog"></i>
                Preferences
            </h3>
            <ul class="preferences-list">
                <li class="preference-item">
                    <span class="preference-label">Difficulty Level</span>
                    <span class="preference-value"><?php echo $reading_preferences['difficulty_level']; ?></span>
                </li>
                <li class="preference-item">
                    <span class="preference-label">Monthly Goal</span>
                    <span class="preference-value"><?php echo $reading_preferences['reading_goal']; ?> books</span>
                </li>
                <li class="preference-item">
                    <span class="preference-label">Current Streak</span>
                    <span class="preference-value"><?php echo $reading_preferences['current_streak']; ?> days</span>
                </li>
                <li class="preference-item">
                    <span class="preference-label">Books Read</span>
                    <span class="preference-value"><?php echo $reading_preferences['total_books_read']; ?></span>
                </li>
            </ul>

            <div class="reading-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 65%;"></div>
                </div>
                <div class="progress-text">Monthly Goal: 65% Complete</div>
            </div>
        </div>

        <!-- Favorite Categories -->
        <div class="sidebar-card">
            <h3 class="card-title">
                <i class="fas fa-heart"></i>
                Favorite Categories
            </h3>
            <div class="book-tags">
                <?php foreach ($reading_preferences['preferred_categories'] as $category): ?>
                    <span class="tag"><?php echo htmlspecialchars($category); ?></span>
                <?php endforeach; ?>
            </div>
            <button class="action-btn btn-secondary" style="width: 100%; margin-top: 15px;" onclick="updatePreferences()">
                <i class="fas fa-edit"></i> Update Preferences
            </button>
        </div>
    </div>
</div>

<script>
    function filterRecommendations(category) {
        // Update active tab
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-category="${category}"]`).classList.add('active');

        // Filter logic would go here for different categories
        console.log('Filtering by category:', category);

        // In real implementation, make AJAX call to get filtered recommendations
    }

    function sortRecommendations(sortBy) {
        const container = document.getElementById('recommendations-container');
        const cards = Array.from(container.querySelectorAll('.book-card'));

        cards.sort((a, b) => {
            switch (sortBy) {
                case 'score':
                    return parseInt(b.getAttribute('data-score')) - parseInt(a.getAttribute('data-score'));
                case 'rating':
                    return parseFloat(b.getAttribute('data-rating')) - parseFloat(a.getAttribute('data-rating'));
                case 'title':
                    return a.querySelector('.book-title').textContent.localeCompare(b.querySelector('.book-title').textContent);
                case 'availability':
                    const aAvail = a.querySelector('.availability').textContent.includes('Available') ? 1 : 0;
                    const bAvail = b.querySelector('.availability').textContent.includes('Available') ? 1 : 0;
                    return bAvail - aAvail;
                default:
                    return 0;
            }
        });

        // Re-append sorted cards
        cards.forEach(card => container.appendChild(card));
    }

    function borrowBook(bookId) {
        if (confirm('Do you want to borrow this book?')) {
            alert(`Borrowing book ID: ${bookId}\n\nRedirecting to borrowing process...`);
            // In real implementation, make AJAX call to borrow book
        }
    }

    function reserveBook(bookId) {
        if (confirm('Do you want to reserve this book?')) {
            alert(`Reserving book ID: ${bookId}\n\nYou will be notified when it becomes available.`);
            // In real implementation, make AJAX call to reserve book
        }
    }

    function joinWaitlist(bookId) {
        if (confirm('Do you want to join the waitlist for this book?')) {
            alert(`Joining waitlist for book ID: ${bookId}\n\nYou will be notified when your turn arrives.`);
            // In real implementation, make AJAX call to join waitlist
        }
    }

    function viewDetails(bookId) {
        alert(`Viewing detailed information for book ID: ${bookId}\n\nThis would open a detailed book page.`);
        // In real implementation, redirect to book details page
    }

    function updatePreferences() {
        alert('Opening preferences update page...\n\nHere you can modify your reading preferences and interests.');
        // In real implementation, open preferences modal or redirect to settings
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Sort by recommendation score by default
        sortRecommendations('score');

        // Add smooth animations
        const cards = document.querySelectorAll('.book-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>