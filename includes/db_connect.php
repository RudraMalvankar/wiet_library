<?php
/**
 * Database Connection File
 * WIET Library Management System
 * 
 * This file establishes a PDO connection to the MySQL database
 * Used by all modules (admin, student, public)
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'wiet_library');
define('DB_USER', 'root');
define('DB_PASS', '');  // Change this for production
define('DB_CHARSET', 'utf8mb4');

// Create PDO instance
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,  // Disable persistent connections
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    
    // Check if connection already exists in global scope
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    
    // If in AJAX mode, return JSON error instead of dying
    if (defined('AJAX_MODE') && AJAX_MODE) {
        echo '<div style="background: #dc3545; color: white; padding: 20px; border-radius: 8px; margin: 20px;">';
        echo '<h3><i class="fas fa-database"></i> Database Connection Failed</h3>';
        echo '<p>Unable to connect to the database. Please ensure MySQL is running.</p>';
        echo '<p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>';
        echo '</div>';
        exit;
    }
    
    die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; border: 1px solid #dc3545; border-radius: 5px; background-color: #f8d7da; color: #721c24;'>
            <h2>⚠️ Database Connection Failed</h2>
            <p>Unable to connect to the library database.</p>
            <p><strong>Please ensure:</strong></p>
            <ul>
                <li>XAMPP/MySQL server is running</li>
                <li>Database '<strong>wiet_library</strong>' exists</li>
                <li>Database credentials are correct in <code>includes/db_connect.php</code></li>
            </ul>
            <hr>
            <small><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</small>
        </div>
    ");
}

// Function to get database connection (optional helper)
function getConnection() {
    global $pdo;
    return $pdo;
}
?>
