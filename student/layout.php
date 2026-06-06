<?php
// Student Dashboard PHP File
// This file contains the complete student library dashboard
// Session management and authentication

// Include session check - validates login and prevents unauthorized access
require_once 'student_session_check.php';

// Student information is now available from session check
// Variables available: $student_id, $member_no, $student_name, $student_email, 
// $student_branch, $student_course, $student_prn, $books_issued

// ============================================================
// FETCH UNREAD NOTIFICATIONS COUNT
// ============================================================
$unread_notifications_count = 0;
try {
    require_once '../includes/db_connect.php';
    
    // Count unread admin notifications + system notifications (overdue, due soon)
    $notif_stmt = $pdo->prepare("
        SELECT COUNT(*) as unread_count
        FROM Notifications
        WHERE (MemberNo = ? OR MemberNo IS NULL)
        AND IsRead = 0
    ");
    $notif_stmt->execute([$member_no]);
    $result = $notif_stmt->fetch(PDO::FETCH_ASSOC);
    $unread_admin_notifs = $result['unread_count'] ?? 0;
    
    // Count overdue books as notifications
    $overdue_stmt = $pdo->prepare("
        SELECT COUNT(*) as overdue_count
        FROM Circulation c
        WHERE c.MemberNo = ?
        AND c.Status = 'Active'
        AND c.DueDate < CURRENT_DATE
    ");
    $overdue_stmt->execute([$member_no]);
    $overdue_result = $overdue_stmt->fetch(PDO::FETCH_ASSOC);
    $overdue_count = $overdue_result['overdue_count'] ?? 0;
    
    // Total unread notifications
    $unread_notifications_count = $unread_admin_notifs + $overdue_count;
    
} catch (Exception $e) {
    error_log("Unread notifications count error: " . $e->getMessage());
    $unread_notifications_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CSS Project</title>
  <link rel="stylesheet" href="style.css" />
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
  }

  /* Headings use Poppins font */
  h1,
  h2,
  h3,
  h4,
  h5,
  h6 {
    font-family: "Poppins", sans-serif;
    font-weight: 700;
    /* Bold headings */
  }

  /* Navbar title uses Poppins and bold */
  .navbar-title {
    font-family: "Poppins", sans-serif;
    color: #263c79;
    font-size: 20px;
    font-weight: 700;
    /* Extra bold for navbar */
    margin: 0;
  }

  .web-banner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    overflow: hidden;
    box-sizing: border-box;
    height: 100px;
    /* same as image */
  }

  .web-banner img {
    width: 100%;
    height: 100%;
    /* match parent height */
    object-fit: cover;
    /* fills container, crops edges if needed */
    object-position: center;
    display: block;
  }

  /* Horizontal Navbar */
  .horizontal-navbar {
    position: fixed;
    top: 97px;
    /* Moved up by 3px to close gap */
    left: 0;
    width: 100%;
    height: 45px;
    background-color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    box-sizing: border-box;
    z-index: 999;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-bottom: 1px solid #cfac69;
  }

  .navbar-left {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .sidebar-toggle {
    background: none;
    border: none;
    color: #263c79;
    font-size: 20px;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    display: block;
    /* Always visible for sidebar collapse/expand */
    transition: all 0.3s ease;
    position: relative;
  }

  .sidebar-toggle:hover {
    background-color: rgba(38, 60, 121, 0.1);
    transform: scale(1.1);
  }

  .sidebar-toggle:active {
    transform: scale(0.95);
  }

  /* Tooltip for sidebar toggle */
  .sidebar-toggle::after {
    content: 'Toggle Sidebar';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(5px);
    background: rgba(38, 60, 121, 0.95);
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease, transform 0.3s ease;
    z-index: 1001;
  }

  .sidebar-toggle:hover::after {
    opacity: 1;
    transform: translateX(-50%) translateY(8px);
  }

  .navbar-right {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .welcome-text {
    color: #263c79;
    font-size: 16px;
    margin: 0;
  }

  .logout-btn {
    background-color: transparent;
    border: 2px solid #dc3545;
    color: #dc3545;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0;
    align-self: center;
  }

  .logout-btn:hover {
    background-color: #dc3545;
    color: white;
  }

  /* Sidebar Styles */
  .sidebar {
    position: fixed;
    top: 142px;
    left: 0;
    width: 220px;
    height: calc(100vh - 142px);
    background-color: #263c79;
    overflow-x: hidden;
    overflow-y: auto;
    z-index: 998;
    transition: all 0.3s ease;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
  }

  /* Sidebar collapsed state */
  .sidebar.collapsed {
    width: 60px;
  }

  .sidebar.collapsed .sidebar-link span {
    display: none;
  }

  .sidebar.collapsed .sidebar-icon {
    margin-right: 0;
    font-size: 20px;
  }

  .sidebar.collapsed .sidebar-link {
    justify-content: center;
    padding: 12px 10px;
  }

  .sidebar.collapsed .notification-badge {
    display: none;
  }

  .sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .sidebar-item {
    border-bottom: 1px solid rgba(207, 172, 105, 0.2);
  }

  /* Remove automatic highlighting of first item - let JavaScript handle active states */

  .sidebar-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    /* Reduced padding from 18px to 12px */
    color: white;
    text-decoration: none;
    font-size: 15px;
    /* Slightly smaller font */
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .sidebar-link:hover {
    background-color: rgba(207, 172, 105, 0.2);
    color: #cfac69;
  }

  .sidebar-link.active {
    background-color: #cfac69;
    color: #263c79;
    font-weight: 600;
  }

  .sidebar-icon {
    margin-right: 12px;
    font-size: 18px;
    width: 20px;
    text-align: center;
  }
  /* Notification Badge */
  .notification-badge {
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: 700;
    margin-left: auto;
    min-width: 20px;
    text-align: center;
    line-height: 16px;
  }
  /* Main Content Area */
  .main-content {
    margin-left: 220px;
    margin-top: 142px;
    /* Start at same level as sidebar */
    padding: 5px 20px 20px 20px;
    min-height: calc(100vh - 217px);
    background-color: white;
    position: relative;
    z-index: 2;
    transition: margin-left 0.3s ease;
  }

  .main-content.expanded {
    margin-left: 60px;
  }

  /* Main content headings - extra bold */
  .main-content h1,
  .main-content h2,
  .main-content h3,
  .main-content h4,
  .main-content h5,
  .main-content h6 {
    font-family: "Poppins", sans-serif;
    font-weight: 700;
    color: #263c79;
  }

  .main-content h1 {
    font-weight: 800;
    /* Extra bold for main titles */
  }

  .main-content h2 {
    font-weight: 700;
  }

  /* Watermark Styles */
  .watermark {
    position: fixed;
    left: 220px;
    top: 142px;
    width: calc(100vw - 220px);
    height: calc(100vh - 142px);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.15;
    z-index: 3;
    pointer-events: none;
    user-select: none;
    transition: left 0.3s ease, width 0.3s ease;
  }

  .watermark.expanded {
    left: 60px;
    width: calc(100vw - 60px);
  }

  .watermark img {
    max-width: 200px;
    max-height: 200px;
    width: auto;
    height: auto;
  }

  /* Mobile Responsive */
  @media (max-width: 768px) {
    .web-banner {
      height: 100px;
      /* Match desktop banner height */
    }

    .horizontal-navbar {
      top: 97px;
      /* Match desktop navbar position */
      padding: 0 15px;
    }

    .sidebar {
      top: 142px;
      /* Banner height (100px) + navbar height (45px) - 3px gap = 142px */
      height: calc(100vh - 142px);
      /* Fixed height matching desktop calculation */
      transform: translateX(-100%);
    }

    .sidebar.sidebar-open {
      transform: translateX(0);
    }

    .main-content {
      margin-left: 0;
      margin-top: 142px;
      /* Same as sidebar top */
      min-height: calc(100vh - 142px);
    }

    .watermark {
      left: 0;
      /* No sidebar on mobile */
      width: 100vw;
      /* Full width on mobile */
    }

    .watermark img {
      max-width: 200px;
      /* Smaller watermark on mobile */
      max-height: 200px;
    }

    .sidebar-toggle {
      display: block;
      /* Show toggle button on mobile */
    }

    .navbar-title {
      font-size: 18px;
    }

    .sidebar-toggle {
      display: block;
      /* Show toggle button on mobile */
    }

    .navbar-title {
      font-size: 18px;
    }

    .welcome-text {
      font-size: 14px;
    }

    .logout-btn {
      padding: 6px 12px;
      font-size: 12px;
    }
  }

  @media (max-width: 480px) {
    .web-banner {
      height: 80px;
      /* Even smaller banner for mobile */
    }

    .horizontal-navbar {
      top: 77px;
      /* Position right after smaller banner (80px - 3px gap) */
      height: 50px;
      /* Increased navbar height by 5px for text wrapping */
    }

    .sidebar {
      top: 127px;
      /* Banner height (80px) + navbar height (50px) - 3px gap = 127px */
      height: calc(100vh - 127px);
      /* Fixed height calculation */
    }

    .main-content {
      margin-top: 127px;
      /* Same as sidebar top */
      min-height: calc(100vh - 127px);
    }

    .watermark {
      top: 127px;
      /* Adjusted for smaller banner and navbar */
      height: calc(100vh - 127px);
    }

    .watermark img {
      max-width: 150px;
      /* Even smaller watermark on very small screens */
      max-height: 150px;
    }

    .welcome-text {
      display: none;
      /* Hide welcome text on very small screens */
    }

    .navbar-title {
      font-size: 14px;
      /* Reduced from 16px to 14px for better mobile fit */
    }
  }
</style>

<body>
  <div class="main">
    <!-- Website Banner -->
    <div class="web-banner">
      <img src="../images/Untitled design (10).png" alt="website banner">
    </div>

    <!-- Horizontal Navbar -->
    <nav class="horizontal-navbar">
      <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
          <i class="fas fa-bars"></i>
        </button>
        <h1 class="navbar-title">WIET LIBRARY</h1>
      </div>
      <div class="navbar-right">
        <p class="welcome-text">Welcome, <?php echo htmlspecialchars($student_name); ?></p>
        <a href="student_logout.php" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="#" class="sidebar-link active" data-page="dashboard">
            <i class="sidebar-icon fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="my-books">
            <i class="sidebar-icon fas fa-book"></i>
            <span>My Books</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="borrowing-history">
            <i class="sidebar-icon fas fa-history"></i>
            <span>Borrowing History</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="search-books">
            <i class="sidebar-icon fas fa-search"></i>
            <span>Search Books</span>
          </a>
        </li>
        <!-- <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="e-resources">
            <i class="sidebar-icon fas fa-laptop"></i>
            <span>E-Resources</span>
          </a>
        </li> -->
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="my-footfall">
            <i class="sidebar-icon fas fa-chart-line"></i>
            <span>My Footfall</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="notifications">
            <i class="sidebar-icon fas fa-bell"></i>
            <span>Notifications</span>
            <?php if ($unread_notifications_count > 0): ?>
              <span class="notification-badge"><?php echo $unread_notifications_count; ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="digital-id">
            <i class="sidebar-icon fas fa-id-card"></i>
            <span>Digital ID</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="recommendations">
            <i class="sidebar-icon fas fa-thumbs-up"></i>
            <span>Recommendations</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="my-profile">
            <i class="sidebar-icon fas fa-user"></i>
            <span>My Profile</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="chatbot">
            <i class="sidebar-icon fas fa-robot"></i>
            <span>Library Assistant</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="library-events">
            <i class="sidebar-icon fas fa-calendar"></i>
            <span>Library Events</span>
          </a>
        </li>
      </ul>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content" id="main-content">
      <!-- Content will be loaded here dynamically -->
      <div id="content-area">
        <!-- Default dashboard content or PHP included content -->
      </div>
    </div>

    <!-- Watermark -->
    <div class="watermark" id="watermark">
      <img src="../images/watumull logo.png" alt="Watumull Logo"
        style="display: block;"
        onerror="console.log('Watumull logo failed to load');"
        onload="console.log('Watumull logo loaded successfully');">
    </div>

  </div>

  <script>
    // Sidebar toggle functionality for mobile and navigation
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarToggle = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('main-content');
      const watermark = document.getElementById('watermark');
      const sidebarLinks = document.querySelectorAll('.sidebar-link');
      const contentArea = document.getElementById('content-area');

      // Sidebar toggle: desktop = collapse/expand, mobile = open/close
      if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
          if (window.innerWidth > 768) {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            if (watermark) watermark.classList.toggle('expanded');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
          } else {
            sidebar.classList.toggle('sidebar-open');
          }
        });
      }

      // Restore collapsed state from localStorage on desktop
      if (window.innerWidth > 768) {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
          sidebar.classList.add('collapsed');
          mainContent.classList.add('expanded');
          if (watermark) watermark.classList.add('expanded');
        }
      }

      // Close sidebar when clicking outside on mobile
      document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
          if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('sidebar-open');
          }
        }
      });

      // Sidebar navigation
      sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();

          // Remove active class from all sidebar links
          document.querySelectorAll('.sidebar-link').forEach(l => {
            l.classList.remove('active');
          });

          // Add active class to clicked link
          this.classList.add('active');

          // Get the page data attribute
          const page = this.getAttribute('data-page');

          // Update URL hash to maintain state
          window.location.hash = page;

          // Close mobile sidebar after selection
          if (window.innerWidth <= 768) {
            sidebar.classList.remove('sidebar-open');
          }

          // Load the page content
          loadPage(page);
        });
      });

      // Function to load page content dynamically
      function loadPage(page) {
        // Show loading state
        contentArea.innerHTML = `
          <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
            <p>Loading...</p>
          </div>
        `;

        // Use fetch to load PHP content
        fetch(`../student/${page}.php`)
          .then(response => {
            if (!response.ok) {
              throw new Error(`Page not found: ${page}`);
            }
            return response.text();
          })
          .then(html => {
            contentArea.innerHTML = html;

            // Execute any scripts in the loaded content
            const scripts = contentArea.querySelectorAll('script');
            scripts.forEach(script => {
              const newScript = document.createElement('script');
              if (script.src) {
                newScript.src = script.src;
              } else {
                newScript.textContent = script.textContent;
              }
              document.head.appendChild(newScript);
              setTimeout(() => {
                try {
                  document.head.removeChild(newScript);
                } catch (e) {}
              }, 0);
            });
          })
          .catch(error => {
            console.error('Error loading page:', error);
            contentArea.innerHTML = `
              <div style="text-align: center; padding: 40px; color: #d63384;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i>
                <h3 style="color: #263c79; margin-bottom: 10px;">Page Not Found</h3>
                <p>The requested page "${page}" could not be loaded.</p>
                <p style="font-size: 14px; color: #666;">Please try again or contact support if the problem persists.</p>
              </div>
            `;
          });

        // Update page title
        document.title = `${page.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())} - WIET LIBRARY`;
      }

      // Make loadPage available globally
      window.loadPage = loadPage;

      // Initialize - check URL or set default active state
      let currentPage = 'dashboard'; // Default page

      // Check if there's a hash in URL to determine current page
      if (window.location.hash) {
        currentPage = window.location.hash.substring(1);
      }

      // Set correct active state on page load
      sidebarLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-page') === currentPage) {
          link.classList.add('active');
        }
      });

      // Load the current page content
      loadPage(currentPage);
    });
  </script>
</body>





</html>
