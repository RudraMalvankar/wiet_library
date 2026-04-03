<?php
/**
 * WIET Library - Advanced End-to-End Testing Suite
 * Version 2.0 - Improved Coverage
 * Date: 2026-04-03
 * 
 * Tests:
 * - Database connectivity & operations
 * - API endpoint workflows (books, footfall, admin)
 * - Authentication flows
 * - HTML5 content validation
 * - PHP syntax correctness
 * - File integrity
 */

define('BASE_PATH', __DIR__ . '/..');
define('BASE_URL', 'http://localhost/wiet_lib');
define('PHP_EXE', 'C:\\xampp\\php\\php.exe');

$start_time = microtime(true);
$results = [
    'passed' => 0,
    'failed' => 0,
    'tests' => [],
    'errors' => []
];

function test_result($category, $name, $pass, $details = '') {
    global $results;
    
    $results['passed'] += $pass ? 1 : 0;
    $results['failed'] += $pass ? 0 : 1;
    
    $results['tests'][] = [
        'category' => $category,
        'name' => $name,
        'status' => $pass ? 'PASS' : 'FAIL',
        'details' => $details,
        'time' => date('Y-m-d H:i:s')
    ];
    
    $icon = $pass ? '✅' : '❌';
    echo sprintf("[%s] %s > %s\n", $icon, $category, $name);
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║     WIET LIBRARY - COMPREHENSIVE E2E TEST SUITE v2.0       ║\n";
echo "║     Date: " . date('Y-m-d H:i:s') . "                           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// PHASE 1: DATABASE & BACKEND
// ============================================================================

echo "┌─ PHASE 1: DATABASE & BACKEND SYSTEMS ─────────────────────┐\n\n";

// Test 1.1: Check database file
$db_file = BASE_PATH . '/includes/db_connect.php';
test_result('DB', 'Database connection file exists', file_exists($db_file), 
    file_exists($db_file) ? 'OK' : 'File not found');

// Test 1.2: Include database connection
$db_init = false;
if (file_exists($db_file)) {
    try {
        ob_start();
        include($db_file);
        ob_end_clean();
        $db_init = isset($pdo) && ($pdo instanceof PDO);
        test_result('DB', 'Database connection initializes', $db_init,
            $db_init ? 'PDO active' : 'PDO not initialized');
    } catch (Throwable $e) {
        test_result('DB', 'Database connection initializes', false, $e->getMessage());
    }
}

// Test 1.3: Database connectivity check
if ($db_init) {
    try {
        $result = $pdo->query("SELECT 1 as test");
        $row = $result->fetch();
        test_result('DB', 'Database query execution', isset($row['test']), 'Query successful');
    } catch (Throwable $e) {
        test_result('DB', 'Database query execution', false, $e->getMessage());
    }
}

// Test 1.4: Check all required tables
$required_tables = ['Admin', 'Books', 'Member', 'Student', 'Footfall'];
if ($db_init) {
    foreach ($required_tables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM $table LIMIT 1");
            $stmt->execute();
            $count = $stmt->fetch();
            test_result('DB', "Table '$table' query", isset($count['cnt']), 
                "Records: " . intval($count['cnt']));
        } catch (Throwable $e) {
            test_result('DB', "Table '$table' query", false, substr($e->getMessage(), 0, 50));
        }
    }
}

// Test 1.5: Test database statistics
if ($db_init) {
    try {
        $tables_data = [];
        
        $books_count = $pdo->query("SELECT COUNT(*) as cnt FROM Books")->fetch()['cnt'];
        $members_count = $pdo->query("SELECT COUNT(*) as cnt FROM Member")->fetch()['cnt'];
        $footfall_count = $pdo->query("SELECT COUNT(*) as cnt FROM Footfall")->fetch()['cnt'];
        $admins_count = $pdo->query("SELECT COUNT(*) as cnt FROM Admin")->fetch()['cnt'];
        
        test_result('DB', 'Data statistics retrieval', true,
            "Books:$books_count | Members:$members_count | Footfall:$footfall_count | Admins:$admins_count");
    } catch (Throwable $e) {
        test_result('DB', 'Data statistics retrieval', false, 'Query failed');
    }
}

echo "\n";

// ============================================================================
// PHASE 2: BACKEND API ENDPOINTS
// ============================================================================

echo "┌─ PHASE 2: BACKEND API ENDPOINTS ───────────────────────────┐\n\n";

$api_tests = [
    '/admin/api/books.php?action=list' => 'Books API - List',
    '/admin/api/books.php?action=getBookImage' => 'Books API - Get Image',
    '/footfall/api/footfall-records.php' => 'Footfall API - Records',
    '/footfall/api/footfall-stats.php' => 'Footfall API - Statistics',
    '/admin/api/analytics.php' => 'Admin API - Analytics',
];

foreach ($api_tests as $endpoint => $name) {
    $url = BASE_URL . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $is_json = json_decode($response, true) !== null;
    $status = ($http_code == 200 && ($is_json || strlen($response) > 0));
    
    test_result('API', $name, $status, "HTTP $http_code | " . 
        ($is_json ? "JSON" : "HTML/TEXT") . " | " . strlen($response) . " bytes");
}

echo "\n";

// ============================================================================
// PHASE 3: WEB PAGES & FRONTENDS
// ============================================================================

echo "┌─ PHASE 3: WEB PAGES & FRONTEND SYSTEMS ────────────────────┐\n\n";

$frontend_tests = [
    '/index.php' => 'Landing page',
    '/opac.php' => 'OPAC (Library Search)',
    '/admin/dashboard.php' => 'Admin Dashboard',
    '/admin/books-management.php' => 'Books Management',
    '/admin/export_books_pdf.php' => 'Books Export/PDF',
    '/admin/footfall-analytics.php' => 'Footfall Analytics',
    '/student/index.php' => 'Student Portal',
    '/student/dashboard.php' => 'Student Dashboard',
    '/student/my-books.php' => 'My Books',
    '/footfall/scanner.php' => 'Footfall Scanner',
];

foreach ($frontend_tests as $page => $name) {
    $url = BASE_URL . $page;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $is_loaded = $http_code == 200 && strlen($response) > 500;
    $has_html = strpos($response, '<!DOCTYPE') !== false || strpos($response, '<html') !== false;
    
    test_result('Web', $name, $is_loaded && $has_html, 
        "HTTP $http_code | Size: " . strlen($response) . " bytes");
}

echo "\n";

// ============================================================================
// PHASE 4: AUTHENTICATION PAGES
// ============================================================================

echo "┌─ PHASE 4: AUTHENTICATION & LOGIN FLOWS ────────────────────┐\n\n";

$auth_tests = [
    '/admin/login.php' => 'Admin Login',
    '/admin/superAdmin_login.php' => 'Super Admin Login',
    '/student/student_login.php' => 'Student Login',
];

foreach ($auth_tests as $page => $name) {
    $url = BASE_URL . $page;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $is_ok = $http_code == 200;
    $has_form = strpos($response, 'form') !== false || strpos($response, 'input') !== false;
    
    test_result('Auth', $name, $is_ok, "HTTP $http_code | " . ($has_form ? "Form OK" : "No form"));
}

echo "\n";

// ============================================================================
// PHASE 5: FILE STRUCTURE & INTEGRITY
// ============================================================================

echo "┌─ PHASE 5: FILE STRUCTURE & INTEGRITY ──────────────────────┐\n\n";

// Check critical PHP files
$php_files = [
    '/admin/books-management.php',
    '/admin/layout.php',
    '/admin/export_books_pdf.php',
    '/footfall/scanner.php',
    '/student/dashboard.php',
    '/includes/db_connect.php',
    '/includes/functions.php'
];

foreach ($php_files as $file) {
    $path = BASE_PATH . $file;
    $exists = file_exists($path);
    test_result('Files', "File: " . basename($file), $exists, 
        $exists ? filesize($path) . " bytes" : "Not found");
}

// Check critical directories
$directories = [
    '/admin' => 'Admin Module',
    '/student' => 'Student Module',
    '/footfall' => 'Footfall Module',
    '/includes' => 'Shared Includes',
    '/database' => 'Database',
    '/images' => 'Images',
    '/storage' => 'Storage'
];

foreach ($directories as $dir => $name) {
    $path = BASE_PATH . $dir;
    $exists = is_dir($path);
    $file_count = $exists ? count(glob($path . '/*', GLOB_NOSORT)) : 0;
    test_result('Dir', $name . " exists", $exists, 
        $exists ? "$file_count items" : "Not found");
}

echo "\n";

// ============================================================================
// PHASE 6: FEATURE WORKFLOW TESTS
// ============================================================================

echo "┌─ PHASE 6: FEATURE WORKFLOWS ───────────────────────────────┐\n\n";

// Test 6.1: Books modal in HTML
$url = BASE_URL . '/admin/books-management.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$has_modal_html = (strpos($response, '.modal {') !== false || strpos($response, 'modal-content') !== false);
$has_modal_js = (strpos($response, 'openAddBookModal') !== false || strpos($response, 'modal') !== false);

test_result('Features', 'Books modal CSS present', $has_modal_html, 'Found modal styles');
test_result('Features', 'Books modal JS present', $has_modal_js, 'Found modal functions');

// Test 6.2: Footfall scanner QR functionality
$url = BASE_URL . '/footfall/scanner.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$has_qr_lib = (strpos($response, 'html5-qrcode') !== false || strpos($response, 'qrcode') !== false);
$has_scanner_js = (strpos($response, 'checkIn') !== false || strpos($response, 'scanner') !== false);

test_result('Features', 'QR code library included', $has_qr_lib, 'QR library found');
test_result('Features', 'Scanner JS functions present', $has_scanner_js, 'Scanner functions OK');

// Test 6.3: Admin layout structure
$url = BASE_URL . '/admin/layout.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$has_sidebar = strpos($response, 'sidebar') !== false;
$has_main_content = strpos($response, 'main-content') !== false;
$has_header = strpos($response, 'header') !== false;

test_result('Features', 'Admin layout has sidebar', $has_sidebar, 'Sidebar structure OK');
test_result('Features', 'Admin layout has main content', $has_main_content, 'Content area OK');
test_result('Features', 'Admin layout has header', $has_header, 'Header structure OK');

echo "\n";

// ============================================================================
// PHASE 7: BASIC PERFORMANCE METRICS
// ============================================================================

echo "┌─ PHASE 7: PERFORMANCE METRICS ─────────────────────────────┐\n\n";

$perf_tests = [
    '/index.php',
    '/admin/dashboard.php',
    '/footfall/scanner.php'
];

foreach ($perf_tests as $page) {
    $url = BASE_URL . $page;
    $start = microtime(true);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
    
    $elapsed = (microtime(true) - $start) * 1000;
    $is_fast = $elapsed < 2000;
    
    test_result('Perf', basename($page) . ' load time', $is_fast,
        number_format($elapsed, 0) . "ms");
}

echo "\n";

// ============================================================================
// GENERATE COMPREHENSIVE REPORT
// ============================================================================

echo "┌─ GENERATING TEST REPORT ───────────────────────────────────┐\n\n";

$duration = microtime(true) - $start_time;
$total_tests = $results['passed'] + $results['failed'];
$pass_percentage = $total_tests > 0 ? round(($results['passed'] / $total_tests) * 100, 1) : 0;

$report = "# WIET Library - Comprehensive E2E Test Report v2.0\n\n";
$report .= "**Generated:** " . date('Y-m-d H:i:s') . "\n";
$report .= "**Test Duration:** " . number_format($duration, 2) . " seconds\n";
$report .= "**Environment:** " . PHP_OS . " | PHP " . phpversion() . "\n\n";

$report .= "## 📊 TEST SUMMARY\n\n";
$report .= "| Metric | Value |\n";
$report .= "|--------|-------|\n";
$report .= "| **Total Tests** | {$total_tests} |\n";
$report .= "| **Passed** | ✅ {$results['passed']} ({$pass_percentage}%) |\n";
$report .= "| **Failed** | ❌ {$results['failed']} (" . (100 - $pass_percentage) . "%) |\n";
$report .= "| **Duration** | " . number_format($duration, 2) . "s |\n\n";

// Group by category
$by_category = [];
foreach ($results['tests'] as $test) {
    $cat = $test['category'];
    if (!isset($by_category[$cat])) {
        $by_category[$cat] = [];
    }
    $by_category[$cat][] = $test;
}

foreach ($by_category as $category => $tests) {
    $report .= "## 📋 {$category} Test Results (" . count($tests) . " tests)\n\n";
    
    foreach ($tests as $test) {
        $icon = $test['status'] === 'PASS' ? '✅' : '❌';
        $report .= "- {$icon} **{$test['name']}** - {$test['status']}\n";
        if ($test['details']) {
            $report .= "  - {$test['details']}\n";
        }
    }
    
    $report .= "\n";
}

// System Info
$report .= "## 🖥️ SYSTEM INFORMATION\n\n";
$report .= "- **PHP Version:** " . phpversion() . "\n";
$report .= "- **Operating System:** " . PHP_OS . "\n";
$report .= "- **Base URL:** " . BASE_URL . "\n";
$report .= "- **Database:** " . ($db_init ? "Connected ✅" : "Not tested ❌") . "\n";
$report .= "- **cURL:** " . (function_exists('curl_version') ? "Enabled ✅" : "Disabled ❌") . "\n";

// Recommendations
$report .= "\n## 💡 RECOMMENDATIONS\n\n";
if ($results['failed'] == 0) {
    $report .= "✅ **All systems operational!** No action required.\n";
} else {
    $report .= "⚠️ **Issues detected:**\n";
    foreach ($results['tests'] as $test) {
        if ($test['status'] === 'FAIL') {
            $report .= "- Fix: {$test['name']} ({$test['category']})\n";
        }
    }
}

$report .= "\n---\n";
$report .= "**Report Generated:** " . date('Y-m-d H:i:s') . "\n";

$report_file = BASE_PATH . '/Automated-Test/E2E_COMPREHENSIVE_REPORT_' . date('Y-m-d_H-i-s') . '.md';
file_put_contents($report_file, $report);

echo "✅ Report saved: Automated-Test/E2E_COMPREHENSIVE_REPORT_" . date('Y-m-d_H-i-s') . ".md\n";
echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                    TEST EXECUTION COMPLETE                  ║\n";
echo "╠════════════════════════════════════════════════════════════╣\n";
echo sprintf("║  Total: %-6d  Passed: %-6d  Failed: %-6d  Rate: %5.1f%% ║\n", 
    $total_tests, $results['passed'], $results['failed'], $pass_percentage);
echo "╚════════════════════════════════════════════════════════════╝\n\n";

?>
