<?php
// Student Dashboard PHP File
// This file contains the complete student library dashboard
// Session management and authentication would be added here

// Start session for user authentication
session_start();

// Placeholder for authentication check
// if (!isset($_SESSION['student_id'])) {
//     header('Location: login.php');
//     exit();
// }

// Get admin information from session
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
$admin_id = $_SESSION['admin_id'] ?? 'ADM2024001';
$is_superadmin = $_SESSION['is_superadmin'] ?? false;
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
    display: none;
    /* Hidden by default, shown only on mobile */
  }

  .sidebar-toggle:hover {
    background-color: rgba(38, 60, 121, 0.1);
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
    /* Fixed positioning so it doesn't scroll */
    top: 142px;
    /* Distance from top of page */
    left: 0;
    width: 220px;
    height: calc(100vh - 142px);
    /* Fixed height to fill viewport */
    background-color: #263c79;
    overflow-y: auto;
    /* Enable vertical scrolling when needed */
    overflow-x: hidden;
    /* Hide horizontal scroll */
    z-index: 998;
    transition: transform 0.3s ease;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
  }

  .sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    /* Remove min-height - let content determine height */
    display: flex;
    flex-direction: column;
  }

  .sidebar-item {
    border-bottom: 1px solid rgba(207, 172, 105, 0.2);
    flex-shrink: 0;
    /* Prevent items from shrinking */
  }

  /* Remove border-bottom from the last item (Settings) */
  .sidebar-item:last-child {
    border-bottom: none;
  }

  .sidebar-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: white;
    text-decoration: none;
    font-size: 15px;
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

  /* Custom scrollbar for sidebar */
  .sidebar {
    scrollbar-width: thin;
    scrollbar-color: #cfac69 #263c79;
  }

  .sidebar::-webkit-scrollbar {
    width: 6px;
    background: #263c79;
  }

  .sidebar::-webkit-scrollbar-thumb {
    background: #cfac69;
    border-radius: 6px;
  }

  .sidebar::-webkit-scrollbar-track {
    background: #263c79;
  }

  /* Main Content Area */
  .main-content {
    margin-left: 220px;
    /* Space for sidebar */
    margin-top: 142px;
    /* Space for banner and navbar */
    min-height: calc(100vh - 142px);
    padding: 5px 20px 20px;
    background-color: #f8f9fa;
    position: relative;
    z-index: 1;
    /* Ensure content is above watermark */
  }

  /* Watermark - Fixed positioning to stay behind content */
  .watermark {
    position: fixed;
    top: 142px;
    /* Below navbar */
    left: 220px;
    /* After sidebar */
    width: calc(100vw - 220px);
    /* Full width minus sidebar */
    height: calc(100vh - 142px);
    /* Full height minus top elements */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    /* Behind all content */
    pointer-events: none;
    /* Allow clicking through */
    background: transparent;
  }

  .watermark img {
    max-width: 200px;
    max-height: 200px;
    opacity: 0.15;
    /* Very faint */

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
      height: calc(100vh - 142px);
      transform: translateX(-100%);
      transition: transform 0.3s ease;
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
      opacity: 0.25;
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
      /* Increased navba ight by 5px for text wrapping */
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
      opacity: 0.2;
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
        <h1 class="navbar-title">WIET College Library</h1>
      </div>
      <div class="navbar-right">
        <p class="welcome-text">Welcome, <?php 
            if ($is_superadmin && strpos($admin_name, 'Super Admin') === false) {
                echo htmlspecialchars($admin_name) . ' (Super Admin)';
            } else {
                echo htmlspecialchars($admin_name);
            }
        ?></p>
        <a href="../index.php" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="dashboard">
            <i class="sidebar-icon fas fa-tachometer-alt"></i>
            Dashboard
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="analytics">
            <i class="sidebar-icon fas fa-chart-bar"></i>
            Analytics
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="books-management">
            <i class="sidebar-icon fas fa-book-open"></i>
            Books Management
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="student-management">
            <i class="sidebar-icon fas fa-user-graduate"></i>
            Student Management
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="circulation">
            <i class="sidebar-icon fas fa-exchange-alt"></i>
            Circulation
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="members">
            <i class="sidebar-icon fas fa-users"></i>
            Members
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="book-assignments">
            <i class="sidebar-icon fas fa-clipboard-list"></i>
            Book Assignments
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="library-events">
            <i class="sidebar-icon fas fa-calendar-alt"></i>
            Library Events
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="bulk-import">
            <i class="sidebar-icon fas fa-upload"></i>
            Bulk Import
          </a>
        </li>
        <?php if ($is_superadmin): ?>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="manage-admins">
            <i class="sidebar-icon fas fa-user-shield"></i>
            Admin Management
          </a>
        </li>
        <?php endif; ?>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="notifications">
            <i class="sidebar-icon fas fa-bell"></i>
            Notifications
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="settings">
            <i class="sidebar-icon fas fa-cog"></i>
            Settings
          </a>
        </li>
      </ul>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content" id="main-content">
      <!-- Content will be loaded here dynamically -->
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
      const sidebarLinks = document.querySelectorAll('.sidebar-link');
      const mainContent = document.getElementById('main-content');

      // Mobile sidebar toggle
      if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
          if (sidebar) {
            sidebar.classList.toggle('sidebar-open');
          }
        });
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

          // Remove active class from all links
          sidebarLinks.forEach(l => l.classList.remove('active'));

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

      // Function to load page content (you can modify this for PHP includes)
      function loadPage(page) {
        // Use fetch to load PHP content dynamically
        fetch(`${page}.php`)
          .then(response => {
            if (!response.ok) {
              throw new Error(`Page not found: ${page}`);
            }
            return response.text();
          })
          .then(html => {
            mainContent.innerHTML = html;
            // Execute any scripts in the loaded content
            const scripts = mainContent.querySelectorAll('script');
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
              // Try calling common initialization functions from the loaded page
              try {
                const candidateFns = [
                  `${page}Init`,
                  `init_${page.replace(/-/g, '_')}`,
                  `${page.replace(/-/g, '_')}Init`,
                  'initPage',
                  'loadBooksTable'
                ];
                candidateFns.forEach(fnName => {
                  if (typeof window[fnName] === 'function') {
                    try {
                      window[fnName]();
                    } catch (e) {
                      console.warn('Error calling init function', fnName, e);
                    }
                  }
                });
              } catch (e) {
                console.warn('Error while attempting to call page init functions', e);
              }
          })
          .catch(error => {
            console.error('Error loading page:', error);
            mainContent.innerHTML = `
              <div style="text-align: center; padding: 40px; color: #d63384; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i>
                <h3 style="color: #263c79; margin-bottom: 10px;">Page Not Found</h3>
                <p>The requested page "${page}" could not be loaded.</p>
                <p style="font-size: 14px; color: #666;">Please try again or contact support if the problem persists.</p>
              </div>
            `;
          });
        // Update page title
        document.title = `${page.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())} - WIET College Library`;
      }

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