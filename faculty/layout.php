<?php
require_once 'faculty_session_check.php';
$unread_notifications_count = 0;
try {
    require_once '../includes/db_connect.php';
    $notif_stmt = $pdo->prepare("
        SELECT COUNT(*) as unread_count
        FROM Notifications
        WHERE (MemberNo = ? OR MemberNo IS NULL) AND IsRead = 0
    ");
    $notif_stmt->execute([$member_no]);
    $result = $notif_stmt->fetch(PDO::FETCH_ASSOC);
    $unread_notifs = $result['unread_count'] ?? 0;

    $overdue_stmt = $pdo->prepare("
        SELECT COUNT(*) as overdue_count
        FROM Circulation c
        WHERE c.MemberNo = ? AND c.Status = 'Active' AND c.DueDate < CURRENT_DATE
    ");
    $overdue_stmt->execute([$member_no]);
    $overdue_result = $overdue_stmt->fetch(PDO::FETCH_ASSOC);
    $overdue_count = $overdue_result['overdue_count'] ?? 0;

    $unread_notifications_count = $unread_notifs + $overdue_count;
} catch (Exception $e) {
    error_log("Unread notifications count error: " . $e->getMessage());
    $unread_notifications_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Faculty Portal - WIET Library</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "Lato", sans-serif; font-weight: 400; font-style: normal; }
    h1, h2, h3, h4, h5, h6 { font-family: "Poppins", sans-serif; font-weight: 700; }
    .navbar-title { font-family: "Poppins", sans-serif; color: #263c79; font-size: 20px; font-weight: 700; margin: 0; }
    .web-banner {
      position: fixed; top: 0; left: 0; width: 100%; z-index: 1000;
      overflow: hidden; height: 100px;
    }
    .web-banner img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
    .horizontal-navbar {
      position: fixed; top: 97px; left: 0; width: 100%; height: 45px;
      background-color: white; display: flex; align-items: center;
      justify-content: space-between; padding: 0 20px; z-index: 999;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-bottom: 1px solid #cfac69;
    }
    .navbar-left { display: flex; align-items: center; gap: 15px; }
    .sidebar-toggle {
      background: none; border: none; color: #263c79; font-size: 20px;
      cursor: pointer; padding: 8px; border-radius: 4px; transition: all 0.3s ease;
    }
    .sidebar-toggle:hover { background-color: rgba(38,60,121,0.1); transform: scale(1.1); }
    .navbar-right { display: flex; align-items: center; gap: 15px; }
    .welcome-text { color: #263c79; font-size: 16px; margin: 0; }
    .logout-btn {
      background-color: transparent; border: 2px solid #dc3545; color: #dc3545;
      padding: 6px 12px; border-radius: 4px; font-size: 14px; font-weight: 600;
      cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 5px;
    }
    .logout-btn:hover { background-color: #dc3545; color: white; }
    .sidebar {
      position: fixed; top: 142px; left: 0; width: 220px;
      height: calc(100vh - 142px); background-color: #263c79;
      overflow-x: hidden; overflow-y: auto; z-index: 998;
      transition: all 0.3s ease; box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    .sidebar.collapsed { width: 60px; }
    .sidebar.collapsed .sidebar-link span { display: none; }
    .sidebar.collapsed .sidebar-icon { margin-right: 0; font-size: 20px; }
    .sidebar.collapsed .sidebar-link { justify-content: center; padding: 12px 10px; }
    .sidebar.collapsed .notification-badge { display: none; }
    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-item { border-bottom: 1px solid rgba(207,172,105,0.2); }
    .sidebar-link {
      display: flex; align-items: center; padding: 12px 20px;
      color: white; text-decoration: none; font-size: 15px;
      font-weight: 500; transition: all 0.3s ease; cursor: pointer;
    }
    .sidebar-link:hover { background-color: rgba(207,172,105,0.2); color: #cfac69; }
    .sidebar-link.active { background-color: #cfac69; color: #263c79; font-weight: 600; }
    .sidebar-icon { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
    .notification-badge {
      background-color: #dc3545; color: white; border-radius: 50%;
      padding: 2px 6px; font-size: 11px; font-weight: 700;
      margin-left: auto; min-width: 20px; text-align: center;
    }
    .main-content {
      margin-left: 220px; margin-top: 142px;
      padding: 5px 20px 20px 20px;
      min-height: calc(100vh - 217px); background-color: white;
      position: relative; z-index: 2; transition: margin-left 0.3s ease;
    }
    .main-content.expanded { margin-left: 60px; }
    .main-content h1, .main-content h2, .main-content h3 { color: #263c79; }
    .watermark {
      position: fixed; left: 220px; top: 142px;
      width: calc(100vw - 220px); height: calc(100vh - 142px);
      display: flex; align-items: center; justify-content: center;
      opacity: 0.15; z-index: 3; pointer-events: none; user-select: none;
      transition: left 0.3s ease, width 0.3s ease;
    }
    .watermark.expanded { left: 60px; width: calc(100vw - 60px); }
    .watermark img { max-width: 200px; max-height: 200px; }
    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.sidebar-open { transform: translateX(0); }
      .main-content { margin-left: 0; }
      .watermark { left: 0; width: 100vw; }
      .welcome-text { font-size: 14px; }
    }
  </style>
</head>
<body>
  <div class="main">
    <div class="web-banner">
      <img src="../images/Untitled design (10).png" alt="website banner">
    </div>
    <nav class="horizontal-navbar">
      <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h1 class="navbar-title">WIET LIBRARY - FACULTY PORTAL</h1>
      </div>
      <div class="navbar-right">
        <p class="welcome-text">Welcome, <?php echo htmlspecialchars($faculty_name); ?></p>
        <a href="faculty_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </nav>
    <aside class="sidebar" id="sidebar">
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="#" class="sidebar-link active" data-page="dashboard">
            <i class="sidebar-icon fas fa-tachometer-alt"></i><span>Dashboard</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="my-books">
            <i class="sidebar-icon fas fa-book"></i><span>My Books</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="borrowing-history">
            <i class="sidebar-icon fas fa-history"></i><span>Borrowing History</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="search-books">
            <i class="sidebar-icon fas fa-search"></i><span>Search Books</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="notifications">
            <i class="sidebar-icon fas fa-bell"></i><span>Notifications</span>
            <?php if ($unread_notifications_count > 0): ?>
              <span class="notification-badge"><?php echo $unread_notifications_count; ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="sidebar-item">
          <a href="#" class="sidebar-link" data-page="my-profile">
            <i class="sidebar-icon fas fa-user"></i><span>My Profile</span>
          </a>
        </li>
      </ul>
    </aside>
    <div class="main-content" id="main-content">
      <div id="content-area"></div>
    </div>
    <div class="watermark" id="watermark">
      <img src="../images/watumull logo.png" alt="Watumull Logo">
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarToggle = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('main-content');
      const watermark = document.getElementById('watermark');
      const sidebarLinks = document.querySelectorAll('.sidebar-link');
      const contentArea = document.getElementById('content-area');

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

      if (window.innerWidth > 768) {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
          sidebar.classList.add('collapsed');
          mainContent.classList.add('expanded');
          if (watermark) watermark.classList.add('expanded');
        }
      }

      document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
          if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('sidebar-open');
          }
        }
      });

      sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
          this.classList.add('active');
          const page = this.getAttribute('data-page');
          window.location.hash = page;
          if (window.innerWidth <= 768) sidebar.classList.remove('sidebar-open');
          loadPage(page);
        });
      });

      function loadPage(page) {
        contentArea.innerHTML = '<div style="text-align:center;padding:40px;color:#666;"><i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:10px;"></i><p>Loading...</p></div>';
        fetch(`../faculty/${page}.php`)
          .then(response => {
            if (!response.ok) throw new Error(`Page not found: ${page}`);
            return response.text();
          })
          .then(html => {
            contentArea.innerHTML = html;
            const scripts = contentArea.querySelectorAll('script');
            scripts.forEach(script => {
              const newScript = document.createElement('script');
              if (script.src) newScript.src = script.src;
              else newScript.textContent = script.textContent;
              document.head.appendChild(newScript);
              setTimeout(() => { try { document.head.removeChild(newScript); } catch(e) {} }, 0);
            });
          })
          .catch(error => {
            contentArea.innerHTML = '<div style="text-align:center;padding:40px;color:#d63384;"><i class="fas fa-exclamation-triangle" style="font-size:48px;margin-bottom:15px;"></i><h3 style="color:#263c79;margin-bottom:10px;">Page Not Found</h3><p>The requested page could not be loaded.</p></div>';
          });
        document.title = `${page.replace('-',' ').replace(/\b\w/g,l=>l.toUpperCase())} - WIET LIBRARY`;
      }
      window.loadPage = loadPage;

      let currentPage = 'dashboard';
      if (window.location.hash) currentPage = window.location.hash.substring(1);
      sidebarLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-page') === currentPage) link.classList.add('active');
      });
      loadPage(currentPage);
    });
  </script>
</body>
</html>
