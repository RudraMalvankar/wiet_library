<?php
// E-Resources Content - Digital library resources and online databases
// This file will be included in the main content area

// Session management
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: student_login.php');
    exit();
}

// Get student info from session
$student_name = $_SESSION['student_name'] ?? 'Student';
$student_id = $_SESSION['student_id'] ?? null;

// E-Resources data
$databases = [
    [
        'name' => 'IEEE Xplore Digital Library',
        'description' => 'Full-text access to IEEE journals, conference papers, and standards',
        'category' => 'Engineering & Technology',
        'access_type' => 'Full Access',
        'icon' => 'fas fa-microchip',
        'url' => '#',
        'status' => 'active'
    ],
    [
        'name' => 'ACM Digital Library',
        'description' => 'Computing and information technology research publications',
        'category' => 'Computer Science',
        'access_type' => 'Full Access',
        'icon' => 'fas fa-laptop-code',
        'url' => '#',
        'status' => 'active'
    ],
    [
        'name' => 'SpringerLink',
        'description' => 'Scientific, technical and medical research content',
        'category' => 'Science & Technology',
        'access_type' => 'Limited Access',
        'icon' => 'fas fa-flask',
        'url' => '#',
        'status' => 'active'
    ],
    [
        'name' => 'ScienceDirect',
        'description' => 'Full-text scientific database offering journal articles and book chapters',
        'category' => 'Science & Engineering',
        'access_type' => 'Full Access',
        'icon' => 'fas fa-atom',
        'url' => '#',
        'status' => 'active'
    ],
    [
        'name' => 'JSTOR Academic',
        'description' => 'Digital library of academic journals and books',
        'category' => 'Multidisciplinary',
        'access_type' => 'Full Access',
        'icon' => 'fas fa-graduation-cap',
        'url' => '#',
        'status' => 'maintenance'
    ],
    [
        'name' => 'ProQuest Central',
        'description' => 'Multidisciplinary database with scholarly journals and dissertations',
        'category' => 'Multidisciplinary',
        'access_type' => 'Full Access',
        'icon' => 'fas fa-search',
        'url' => '#',
        'status' => 'active'
    ]
];

$ebooks = [
    [
        'title' => 'Computer Networks: A Systems Approach',
        'author' => 'Peterson & Davie',
        'category' => 'Computer Science',
        'format' => 'PDF',
        'size' => '15.2 MB',
        'downloads' => 234,
        'url' => '#'
    ],
    [
        'title' => 'Digital Signal Processing',
        'author' => 'Proakis & Manolakis',
        'category' => 'Electronics',
        'format' => 'EPUB',
        'size' => '8.7 MB',
        'downloads' => 189,
        'url' => '#'
    ],
    [
        'title' => 'Software Engineering: A Practitioner\'s Approach',
        'author' => 'Roger Pressman',
        'category' => 'Software Engineering',
        'format' => 'PDF',
        'size' => '22.1 MB',
        'downloads' => 312,
        'url' => '#'
    ],
    [
        'title' => 'Database System Concepts',
        'author' => 'Silberschatz, Korth & Sudarshan',
        'category' => 'Database',
        'format' => 'PDF',
        'size' => '18.9 MB',
        'downloads' => 278,
        'url' => '#'
    ]
];

$access_stats = [
    'total_resources' => 15000,
    'databases_available' => 12,
    'ebooks_accessed' => 45,
    'this_month_downloads' => 18
];

$recent_access = [
    [
        'resource' => 'IEEE Xplore Digital Library',
        'type' => 'Database',
        'accessed_on' => '2024-01-20 14:30',
        'duration' => '45 min'
    ],
    [
        'resource' => 'Computer Networks: A Systems Approach',
        'type' => 'E-book',
        'accessed_on' => '2024-01-19 10:15',
        'duration' => 'Downloaded'
    ],
    [
        'resource' => 'ScienceDirect',
        'type' => 'Database',
        'accessed_on' => '2024-01-18 16:20',
        'duration' => '25 min'
    ]
];
?>

<style>
    /* E-Resources specific styles */
    .e-resources-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #cfac69;
    }

    .e-resources-title {
        color: #263c79;
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 5px 0;
    }

    .e-resources-subtitle {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        height: 100px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid #cfac69;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .stat-number {
        font-size: 26px;
        font-weight: 700;
        color: #263c79;
        margin-bottom: 8px;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
        font-weight: 500;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    /* Search Form Styles (matching search-books.php) */
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
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .clear-btn:hover {
        border-color: #cfac69;
        color: #263c79;
    }

    .results-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
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

    .databases-section,
    .sidebar-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .section-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-title {
        color: #263c79;
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .view-all-btn {
        background: #263c79;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .view-all-btn:hover {
        background: #1e2f5a;
        text-decoration: none;
        color: white;
    }

    .databases-content {
        padding: 25px;
    }

    .search-filter {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .search-input {
        flex: 1;
        min-width: 250px;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #263c79;
    }

    .filter-select {
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        min-width: 150px;
    }

    .database-grid {
        display: grid;
        gap: 20px;
    }

    .database-item {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        transition: all 0.3s ease;
        position: relative;
    }

    .database-item:hover {
        border-color: #cfac69;
        box-shadow: 0 4px 15px rgba(207, 172, 105, 0.2);
    }

    .database-header {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 15px;
    }

    .database-icon {
        width: 50px;
        height: 50px;
        background: #f8f9fa;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #263c79;
        font-size: 20px;
        flex-shrink: 0;
    }

    .database-info {
        flex: 1;
    }

    .database-name {
        font-size: 16px;
        font-weight: 600;
        color: #263c79;
        margin-bottom: 5px;
    }

    .database-description {
        color: #666;
        font-size: 13px;
        line-height: 1.4;
        margin-bottom: 10px;
    }

    .database-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: #666;
    }

    .access-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .access-full {
        background: #d4edda;
        color: #155724;
    }

    .access-limited {
        background: #fff3cd;
        color: #856404;
    }

    .status-indicator {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .status-active {
        background: #28a745;
    }

    .status-maintenance {
        background: #ffc107;
    }

    .database-actions {
        display: flex;
        gap: 10px;
    }

    .access-btn {
        background: #263c79;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .access-btn:hover {
        background: #1e2f5a;
        text-decoration: none;
        color: white;
    }

    .access-btn.disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .bookmark-btn {
        background: transparent;
        color: #666;
        border: 2px solid #e0e0e0;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .bookmark-btn:hover {
        border-color: #cfac69;
        color: #cfac69;
    }

    .sidebar-content {
        padding: 25px;
    }

    .ebook-item {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .ebook-item:hover {
        border-color: #cfac69;
        background: #fafafa;
    }

    .ebook-title {
        font-size: 14px;
        font-weight: 600;
        color: #263c79;
        margin-bottom: 5px;
        line-height: 1.3;
    }

    .ebook-author {
        color: #666;
        font-size: 12px;
        margin-bottom: 8px;
    }

    .ebook-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .ebook-details {
        display: flex;
        gap: 10px;
        font-size: 11px;
        color: #666;
    }

    .download-count {
        font-size: 11px;
        color: #28a745;
        font-weight: 600;
    }

    .download-ebook-btn {
        background: #28a745;
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }

    .download-ebook-btn:hover {
        background: #218838;
        text-decoration: none;
        color: white;
    }

    .recent-activity {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    /* Activity Icons */
    .activity-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 14px;
        color: white;
        flex-shrink: 0;
    }

    .activity-icon.database {
        background: linear-gradient(135deg, #17a2b8, #6610f2);
    }

    .activity-icon.ebook {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .activity-item {
        padding: 15px 0;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        align-items: flex-start;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-details {
        flex: 1;
    }

    .activity-resource {
        font-weight: 600;
        color: #263c79;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .activity-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
        color: #666;
    }

    .activity-type {
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        text-transform: uppercase;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .content-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

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

        .search-filter {
            flex-direction: column;
            gap: 10px;
        }

        .search-input {
            min-width: auto;
        }

        .database-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 10px;
        }

        .database-actions {
            justify-content: center;
        }
    }
</style>

<div class="e-resources-header">
    <h1 class="e-resources-title">E-Resources</h1>
    <p class="e-resources-subtitle">Access digital library resources, databases, and e-books</p>
</div>

<!-- Statistics Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo number_format($access_stats['total_resources']); ?></div>
        <div class="stat-label">Total Resources</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $access_stats['databases_available']; ?></div>
        <div class="stat-label">Databases Available</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $access_stats['ebooks_accessed']; ?></div>
        <div class="stat-label">E-books Accessed</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $access_stats['this_month_downloads']; ?></div>
        <div class="stat-label">This Month Downloads</div>
    </div>
</div>

<!-- E-Book Search Box (UI matches Search Books) -->
<div class="search-section" style="margin-bottom: 30px;">
    <form class="search-form" id="ebookSearchForm">
        <div class="search-row">
            <div class="search-field">
                <label class="search-label">Title</label>
                <input type="text" class="search-input" id="ebookTitle" placeholder="Enter e-book title...">
            </div>
            <div class="search-field">
                <label class="search-label">Author</label>
                <input type="text" class="search-input" id="ebookAuthor" placeholder="Enter author name...">
            </div>
        </div>
        <div class="search-row">
            <div class="search-field">
                <label class="search-label">Category</label>
                <select class="search-select" id="ebookCategory">
                    <option value="">All Categories</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Software Engineering">Software Engineering</option>
                    <option value="Database">Database</option>
                </select>
            </div>
            <div class="search-field">
                <label class="search-label">Keywords</label>
                <input type="text" class="search-input" id="ebookKeywords" placeholder="Enter keywords, topics, or subjects...">
            </div>
        </div>
        <div class="search-actions">
            <button type="button" class="clear-btn" onclick="clearEbookSearch()">
                <i class="fas fa-eraser"></i>
                Clear
            </button>
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
                Search E-books
            </button>
        </div>
    </form>
</div>

<!-- E-Book Search Results -->
<div class="results-section" id="ebookSearchResults" style="display: none;">
    <div class="results-header">
        <h3 class="results-title">E-book Search Results</h3>
        <span class="results-count" id="ebookResultsCount">0 e-books found</span>
    </div>
    <div id="ebookResultsContainer"></div>
</div>

<div class="content-grid">
    <!-- Main Databases Section -->
    <div class="databases-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-database"></i>
                Online Databases
            </h2>
            <a href="#" class="view-all-btn">View All</a>
        </div>
        <div class="databases-content">
            <div class="search-filter">
                <input type="text" class="search-input" placeholder="Search databases..." id="databaseSearch">
                <select class="filter-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Engineering & Technology">Engineering & Technology</option>
                    <option value="Science & Technology">Science & Technology</option>
                    <option value="Multidisciplinary">Multidisciplinary</option>
                </select>
            </div>

            <div class="database-grid" id="databaseGrid">
                <?php foreach ($databases as $db): ?>
                    <div class="database-item" data-category="<?php echo htmlspecialchars($db['category']); ?>">
                        <div class="status-indicator status-<?php echo $db['status']; ?>"></div>
                        <div class="database-header">
                            <div class="database-icon">
                                <i class="<?php echo $db['icon']; ?>"></i>
                            </div>
                            <div class="database-info">
                                <h3 class="database-name"><?php echo htmlspecialchars($db['name']); ?></h3>
                                <p class="database-description"><?php echo htmlspecialchars($db['description']); ?></p>
                                <div class="database-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-tag"></i>
                                        <?php echo htmlspecialchars($db['category']); ?>
                                    </div>
                                    <span class="access-badge access-<?php echo strtolower(str_replace(' ', '-', $db['access_type'])); ?>">
                                        <?php echo htmlspecialchars($db['access_type']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="database-actions">
                            <a href="<?php echo htmlspecialchars($db['url']); ?>"
                                class="access-btn <?php echo $db['status'] === 'maintenance' ? 'disabled' : ''; ?>"
                                <?php echo $db['status'] === 'maintenance' ? 'onclick="return false;"' : 'target="_blank"'; ?>>
                                <?php echo $db['status'] === 'maintenance' ? 'Under Maintenance' : 'Access Database'; ?>
                            </a>
                            <button class="bookmark-btn" onclick="toggleBookmark(this)">
                                <i class="far fa-bookmark"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar-section">
        <!-- Popular E-books -->
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-book"></i>
                Popular E-books
            </h3>
            <a href="#" class="view-all-btn">View All</a>
        </div>
        <div class="sidebar-content">
            <?php foreach ($ebooks as $ebook): ?>
                <div class="ebook-item">
                    <h4 class="ebook-title"><?php echo htmlspecialchars($ebook['title']); ?></h4>
                    <p class="ebook-author">by <?php echo htmlspecialchars($ebook['author']); ?></p>
                    <div class="ebook-meta">
                        <div class="ebook-details">
                            <span><?php echo htmlspecialchars($ebook['format']); ?></span>
                            <span><?php echo htmlspecialchars($ebook['size']); ?></span>
                        </div>
                        <span class="download-count"><?php echo $ebook['downloads']; ?> downloads</span>
                    </div>
                    <div style="margin-top: 10px;">
                        <a href="<?php echo htmlspecialchars($ebook['url']); ?>" class="download-ebook-btn">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="sidebar-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-history"></i>
            Recent Activity
        </h3>
    </div>
    <div class="sidebar-content">
        <ul class="recent-activity">
            <?php foreach ($recent_access as $activity): ?>
                <li class="activity-item">
                    <div class="activity-icon <?php 
                        $activity_type = strtolower($activity['type']);
                        echo $activity_type == 'database' ? 'database' : 'ebook';
                    ?>">
                        <?php 
                            $activity_type = strtolower($activity['type']);
                            if ($activity_type == 'database') {
                                echo '<i class="fas fa-database"></i>';
                            } else {
                                echo '<i class="fas fa-book-open"></i>';
                            }
                        ?>
                    </div>
                    <div class="activity-details">
                        <div class="activity-resource"><?php echo htmlspecialchars($activity['resource']); ?></div>
                        <div class="activity-meta">
                            <span class="activity-type"><?php echo htmlspecialchars($activity['type']); ?></span>
                            <span><?php echo date('M j, g:i A', strtotime($activity['accessed_on'])); ?></span>
                        </div>
                        <div style="margin-top: 5px; font-size: 11px; color: #666;">
                            Duration: <?php echo htmlspecialchars($activity['duration']); ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
    // Search and filter functionality
    document.getElementById('databaseSearch').addEventListener('input', filterDatabases);
    document.getElementById('categoryFilter').addEventListener('change', filterDatabases);

    function filterDatabases() {
        const searchTerm = document.getElementById('databaseSearch').value.toLowerCase();
        const categoryFilter = document.getElementById('categoryFilter').value;
        const databases = document.querySelectorAll('.database-item');

        databases.forEach(database => {
            const name = database.querySelector('.database-name').textContent.toLowerCase();
            const description = database.querySelector('.database-description').textContent.toLowerCase();
            const category = database.getAttribute('data-category');

            const matchesSearch = name.includes(searchTerm) || description.includes(searchTerm);
            const matchesCategory = !categoryFilter || category === categoryFilter;

            if (matchesSearch && matchesCategory) {
                database.style.display = 'block';
            } else {
                database.style.display = 'none';
            }
        });
    }

    function toggleBookmark(button) {
        const icon = button.querySelector('i');
        if (icon.classList.contains('far')) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.style.color = '#cfac69';
            button.style.borderColor = '#cfac69';
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            button.style.color = '#666';
            button.style.borderColor = '#e0e0e0';
        }
    }

    // E-book search functionality
    function clearEbookSearch() {
        document.getElementById('ebookSearchForm').reset();
        document.getElementById('ebookSearchResults').style.display = 'none';
    }

    function searchEbooks() {
        // Show loading state
        const resultsSection = document.getElementById('ebookSearchResults');
        const resultsContainer = document.getElementById('ebookResultsContainer');
        const resultsCount = document.getElementById('ebookResultsCount');

        resultsSection.style.display = 'block';
        resultsContainer.innerHTML = '<div style="padding: 40px; text-align: center; color: #666;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i><br>Searching e-books...</div>';

        // Simulate search - replace with actual API call
        setTimeout(() => {
            // Mock search results (use the $ebooks array for demo)
            const mockResults = [
                {
                    title: 'Computer Networks: A Systems Approach',
                    author: 'Peterson & Davie',
                    category: 'Computer Science',
                    format: 'PDF',
                    size: '15.2 MB',
                    downloads: 234,
                    url: '#'
                }
            ];

            resultsCount.textContent = `${mockResults.length} e-books found`;

            if (mockResults.length > 0) {
                resultsContainer.innerHTML = `
                <div class='book-grid'>
                    ${mockResults.map(ebook => `
                        <div class='ebook-item'>
                            <h4 class='ebook-title'>${ebook.title}</h4>
                            <p class='ebook-author'>by ${ebook.author}</p>
                            <div class='ebook-meta'>
                                <span>${ebook.format}</span>
                                <span>${ebook.size}</span>
                                <span class='download-count'>${ebook.downloads} downloads</span>
                            </div>
                            <div style='margin-top: 10px;'>
                                <a href='${ebook.url}' class='download-ebook-btn'><i class='fas fa-download'></i> Download</a>
                            </div>
                        </div>
                    `).join('')}
                </div>
                `;
            } else {
                resultsContainer.innerHTML = `
                <div class='no-results'>
                    <i class='fas fa-search'></i>
                    <h3>No E-books Found</h3>
                    <p>Try adjusting your search criteria or browse our popular e-books below.</p>
                </div>
                `;
            }
        }, 1200);
    }

    // Form submission handler
    if (document.getElementById('ebookSearchForm')) {
        document.getElementById('ebookSearchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            searchEbooks();
        });
    }
</script>
