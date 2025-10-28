<?php
/**
 * Page Loader for Layout.php
 * This file loads page content without full HTML structure
 * Prevents duplicate session_start(), HTML tags, and header conflicts
 */

// Session already started in layout.php, suppress errors
@session_start();

// Get the page parameter
$page = $_GET['page'] ?? 'dashboard';

// Sanitize page name (prevent directory traversal)
$page = preg_replace('/[^a-z0-9\-]/', '', $page);

// Map of pages to their actual file names
$pageMap = [
    'dashboard' => 'dashboard.php',
    'analytics' => 'analytics.php',
    'books-management' => 'books-management.php',
    'student-management' => 'student-management.php',
    'circulation' => 'circulation.php',
    'members' => 'members.php',
    'book-assignments' => 'book-assignments.php',
    'fine-management' => 'fine-management.php',
    'reports' => 'reports.php',
    'inventory' => 'inventory.php',
    'stock-verification' => 'stock-verification.php',
    'library-events' => 'library-events.php',
    'bulk-import' => 'bulk-import.php',
    'qr-generator' => 'qr-generator.php',
    'backup-restore' => 'backup-restore.php',
    'manage-admins' => 'manage-admins.php',
    'notifications' => 'notifications.php',
    'change-password' => 'change-password.php',
    'settings' => 'settings.php'
];

// Check if page exists in map
if (!isset($pageMap[$page])) {
    http_response_code(404);
    echo '<div style="text-align: center; padding: 40px;">';
    echo '<h2 style="color: #dc3545;">Page Not Found</h2>';
    echo '<p>The requested page does not exist.</p>';
    echo '</div>';
    exit;
}

$pageFile = __DIR__ . '/' . $pageMap[$page];

// Check if file exists
if (!file_exists($pageFile)) {
    http_response_code(404);
    echo '<div style="text-align: center; padding: 40px;">';
    echo '<h2 style="color: #dc3545;">File Not Found</h2>';
    echo '<p>The page file "' . htmlspecialchars($pageMap[$page]) . '" does not exist.</p>';
    echo '</div>';
    exit;
}

// Read the file content
$fileContent = file_get_contents($pageFile);

// Remove session_start() to prevent conflicts (before including)
$fileContent = preg_replace('/<\?php\s*session_start\(\);\s*\?>/i', '<?php /* session already started */ ?>', $fileContent);
$fileContent = preg_replace('/session_start\(\);/i', '/* session already started */', $fileContent);

// Create a temporary file without session_start
$tempFile = sys_get_temp_dir() . '/page_' . $page . '_' . md5($pageFile) . '.php';
file_put_contents($tempFile, $fileContent);

// Start output buffering
ob_start();

// Include the modified file
include $tempFile;

// Get the content
$content = ob_get_clean();

// Clean up temp file
@unlink($tempFile);

// Extract body content using regex (multiline, case-insensitive, dotall)
if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $matches)) {
    $bodyContent = $matches[1];
} else {
    // If no body tag, check if it's already a partial page
    // Remove any html, head tags that might exist
    $content = preg_replace('/<\/?html[^>]*>/i', '', $content);
    $content = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $content);
    $content = preg_replace('/<\/?body[^>]*>/i', '', $content);
    $content = preg_replace('/<!DOCTYPE[^>]*>/i', '', $content);
    $bodyContent = $content;
}

// Remove any stray html/head/body tags
$bodyContent = preg_replace('/<\/?(?:html|head|body)[^>]*>/i', '', $bodyContent);

// Clean up
$bodyContent = trim($bodyContent);

// Output the cleaned content
header('Content-Type: text/html; charset=UTF-8');
echo $bodyContent;
?>
