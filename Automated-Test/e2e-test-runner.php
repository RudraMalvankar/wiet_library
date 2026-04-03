<?php
/**
 * WIET Library - Comprehensive End-to-End Testing Suite
 * Date: 2026-04-03
 * 
 * This script performs systematic testing across:
 * - Database connectivity and schema
 * - Backend PHP API endpoints
 * - Admin portal functionality
 * - Student portal functionality
 * - Footfall scanner system
 * - Authentication workflows
 * - File operations
 */

// Start timer
$start_time = microtime(true);

// Configuration
define('BASE_URL', 'http://localhost/wiet_lib');
define('REPORT_FILE', __DIR__ . '/E2E_TEST_REPORT_' . date('Y-m-d_H-i-s') . '.md');

// Initialize test results
$test_results = [
    'database' => [],
    'backend' => [],
    'frontend' => [],
    'auth' => [],
    'fileops' => [],
    'summary' => []
];

$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function log_test($category, $test_name, $passed, $details = '') {
    global $test_results, $total_tests, $passed_tests, $failed_tests;
    
    $total_tests++;
    if ($passed) {
        $passed_tests++;
        $status = '✅ PASS';
    } else {
        $failed_tests++;
        $status = '❌ FAIL';
    }
    
    $test_results[$category][] = [
        'name' => $test_name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'details' => $details,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo "[{$status}] {$category} > {$test_name}\n";
    if ($details && !$passed) {
        echo "     {$details}\n";
    }
}

function http_get($url, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return ['body' => $response, 'code' => $http_code, 'error' => $error];
}

function http_post($url, $data = [], $json = false) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($json) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return ['body' => $response, 'code' => $http_code, 'error' => $error];
}

// ============================================================================
// 1. DATABASE CONNECTIVITY TESTS
// ============================================================================

echo "\n=== DATABASE CONNECTIVITY TESTS ===\n";

// Test 1.1: Include database connection
try {
    include('../../includes/db_connect.php');
    log_test('database', 'Database connection file includes successfully', true);
} catch (Exception $e) {
    log_test('database', 'Database connection file includes successfully', false, $e->getMessage());
}

// Test 1.2: PDO connection exists
if (isset($pdo) && $pdo instanceof PDO) {
    log_test('database', 'PDO connection is active', true);
} else {
    log_test('database', 'PDO connection is active', false, 'PDO object not initialized');
}

// Test 1.3: Execute test query
if (isset($pdo)) {
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM Admin LIMIT 1");
        $row = $result->fetch();
        log_test('database', 'Query execution on Admin table', true, "Found {$row['count']} admin records");
    } catch (Exception $e) {
        log_test('database', 'Query execution on Admin table', false, $e->getMessage());
    }
}

// Test 1.4: Check required tables
$required_tables = ['Admin', 'Books', 'Member', 'Student', 'Footfall'];
if (isset($pdo)) {
    foreach ($required_tables as $table) {
        try {
            $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
            log_test('database', "Table exists: $table", true);
        } catch (Exception $e) {
            log_test('database', "Table exists: $table", false, "Table not found or error accessing");
        }
    }
}

// Test 1.5: Database schema validation
if (isset($pdo)) {
    try {
        $schema_query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'wiet_library'";
        $result = $pdo->query($schema_query);
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        log_test('database', 'Database schema validation', true, 'Found ' . count($tables) . ' tables');
    } catch (Exception $e) {
        log_test('database', 'Database schema validation', false, $e->getMessage());
    }
}

// ============================================================================
// 2. BACKEND API ENDPOINT TESTS
// ============================================================================

echo "\n=== BACKEND API ENDPOINT TESTS ===\n";

// Test 2.1: Admin main page accessibility
$response = http_get(BASE_URL . '/admin/dashboard.php');
log_test('backend', 'Admin dashboard page loads', $response['code'] == 200, "HTTP {$response['code']}");

// Test 2.2: Admin books management page
$response = http_get(BASE_URL . '/admin/books-management.php');
log_test('backend', 'Admin books management page loads', in_array($response['code'], [200, 302]), "HTTP {$response['code']}");

// Test 2.3: Admin layout page
$response = http_get(BASE_URL . '/admin/layout.php');
log_test('backend', 'Admin layout page loads', in_array($response['code'], [200, 302]), "HTTP {$response['code']}");

// Test 2.4: Footfall scanner page
$response = http_get(BASE_URL . '/footfall/scanner.php');
log_test('backend', 'Footfall scanner page loads', in_array($response['code'], [200, 302]), "HTTP {$response['code']}");

// Test 2.5: Books API endpoint
$response = http_get(BASE_URL . '/admin/api/books.php?action=list');
log_test('backend', 'Books API endpoint accessible', $response['code'] == 200, "HTTP {$response['code']}");

// Test 2.6: Footfall records API
$response = http_get(BASE_URL . '/footfall/api/footfall-records.php');
log_test('backend', 'Footfall records API accessible', $response['code'] == 200, "HTTP {$response['code']}");

// Test 2.7: Footfall stats API
$response = http_get(BASE_URL . '/footfall/api/footfall-stats.php');
log_test('backend', 'Footfall stats API accessible', $response['code'] == 200, "HTTP {$response['code']}");

// Test 2.8: Export PDF page
$response = http_get(BASE_URL . '/admin/export_books_pdf.php');
log_test('backend', 'Books PDF export page loads', in_array($response['code'], [200, 302]), "HTTP {$response['code']}");

// Test 2.9: Student portal index
$response = http_get(BASE_URL . '/student/index.php');
log_test('backend', 'Student portal loads', in_array($response['code'], [200, 302]), "HTTP {$response['code']}");

// Test 2.10: Footfall index (should redirect)
$response = http_get(BASE_URL . '/footfall/index.php');
log_test('backend', 'Footfall index page exists', in_array($response['code'], [200, 302, 307]), "HTTP {$response['code']}");

// ============================================================================
// 3. FRONTEND PAGE LOAD TESTS
// ============================================================================

echo "\n=== FRONTEND PAGE LOAD TESTS ===\n";

// Test 3.1: Main landing page
$response = http_get(BASE_URL . '/index.php');
log_test('frontend', 'Main landing page loads', $response['code'] == 200, "HTTP {$response['code']}");

// Test 3.2: Landing page contains expected elements
if ($response['code'] == 200) {
    $has_header = strpos($response['body'], 'WIET') !== false;
    log_test('frontend', 'Landing page contains WIET branding', $has_header);
}

// Test 3.3: OPAC page
$response = http_get(BASE_URL . '/opac.php');
log_test('frontend', 'OPAC page loads', $response['code'] == 200, "HTTP {$response['code']}");

// Test 3.4: Admin dashboard loads
$response = http_get(BASE_URL . '/admin/dashboard.php');
log_test('frontend', 'Admin dashboard includes content', strlen($response['body']) > 100);

// ============================================================================
// 4. AUTHENTICATION & SESSION TESTS
// ============================================================================

echo "\n=== AUTHENTICATION & SESSION TESTS ===\n";

// Test 4.1: Admin login page loads
$response = http_get(BASE_URL . '/admin/login.php');
log_test('auth', 'Admin login page loads', in_array($response['code'], [200, 302]), "HTTP {$response['code']}");

// Test 4.2: Student login page loads
$response = http_get(BASE_URL . '/student/login.php');
log_test('auth', 'Student login page loads', in_array($response['code'], [200, 302]), "HTTP {$response['code']}");

// Test 4.3: Superadmin login page
$response = http_get(BASE_URL . '/admin/superAdmin_login.php');
log_test('auth', 'Superadmin login page loads', in_array($response['code'], [200, 302]), "HTTP {$response['code']}");

// ============================================================================
// 5. FILE STRUCTURE VALIDATION TESTS
// ============================================================================

echo "\n=== FILE STRUCTURE VALIDATION TESTS ===\n";

// Test 5.1: Check required directories exist
$base_path = dirname(__DIR__);
$required_dirs = [
    '/admin' => 'Admin portal',
    '/student' => 'Student portal',
    '/footfall' => 'Footfall system',
    '/includes' => 'Shared includes',
    '/database' => 'Database scripts',
    '/images' => 'Images folder',
    '/storage' => 'Storage folder'
];

foreach ($required_dirs as $dir => $name) {
    $path = $base_path . $dir;
    $exists = is_dir($path);
    log_test('fileops', "Directory exists: {$name} ({$dir})", $exists);
}

// Test 5.2: Check critical files
$required_files = [
    '/includes/db_connect.php' => 'Database connection',
    '/includes/functions.php' => 'Shared functions',
    '/admin/layout.php' => 'Admin layout',
    '/admin/books-management.php' => 'Books management',
    '/footfall/scanner.php' => 'Footfall scanner',
    '/student/index.php' => 'Student index'
];

foreach ($required_files as $file => $name) {
    $path = $base_path . $file;
    $exists = file_exists($path);
    log_test('fileops', "File exists: {$name} ({$file})", $exists);
}

// Test 5.3: Check PHP syntax on critical files
$syntax_check_files = [
    '/includes/db_connect.php',
    '/includes/functions.php',
    '/admin/books-management.php',
    '/footfall/scanner.php',
    '/admin/api/books.php'
];

foreach ($syntax_check_files as $file) {
    $path = $base_path . $file;
    if (file_exists($path)) {
        $output = shell_exec("php -l \"$path\" 2>&1");
        $is_valid = strpos($output, 'No syntax errors') !== false;
        log_test('fileops', "PHP syntax check: {$file}", $is_valid, $is_valid ? 'OK' : $output);
    }
}

// ============================================================================
// 6. API RESPONSE VALIDATION TESTS
// ============================================================================

echo "\n=== API RESPONSE VALIDATION TESTS ===\n";

// Test 6.1: Books API returns JSON
$response = http_get(BASE_URL . '/admin/api/books.php?action=list');
if ($response['code'] == 200) {
    $json = json_decode($response['body'], true);
    $is_json = json_last_error() === JSON_ERROR_NONE;
    log_test('backend', 'Books API returns valid JSON', $is_json);
}

// Test 6.2: Footfall API returns JSON
$response = http_get(BASE_URL . '/footfall/api/footfall-records.php');
if ($response['code'] == 200) {
    $json = json_decode($response['body'], true);
    $is_json = json_last_error() === JSON_ERROR_NONE;
    log_test('backend', 'Footfall API returns valid JSON', $is_json);
}

// Test 6.3: Check API response structure
$response = http_get(BASE_URL . '/admin/api/books.php?action=list');
if ($response['code'] == 200) {
    $json = json_decode($response['body'], true);
    if (is_array($json)) {
        $has_expected_keys = isset($json['success']) || isset($json[0]);
        log_test('backend', 'API response has expected structure', $has_expected_keys);
    }
}

// ============================================================================
// 7. DATABASE OPERATIONS TESTS
// ============================================================================

echo "\n=== DATABASE OPERATIONS TESTS ===\n";

if (isset($pdo)) {
    // Test 7.1: Read operation
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM Books");
        $row = $result->fetch();
        log_test('database', 'Books table query', true, "Found {$row['count']} books");
    } catch (Exception $e) {
        log_test('database', 'Books table query', false, $e->getMessage());
    }
    
    // Test 7.2: Read Members
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM Member");
        $row = $result->fetch();
        log_test('database', 'Members table query', true, "Found {$row['count']} members");
    } catch (Exception $e) {
        log_test('database', 'Members table query', false, $e->getMessage());
    }
    
    // Test 7.3: Read Footfall
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM Footfall");
        $row = $result->fetch();
        log_test('database', 'Footfall table query', true, "Found {$row['count']} footfall records");
    } catch (Exception $e) {
        log_test('database', 'Footfall table query', false, $e->getMessage());
    }
    
    // Test 7.4: Test prepared statements
    try {
        $stmt = $pdo->prepare("SELECT * FROM Books LIMIT 1");
        $stmt->execute();
        $book = $stmt->fetch();
        log_test('database', 'Prepared statement execution', true);
    } catch (Exception $e) {
        log_test('database', 'Prepared statement execution', false, $e->getMessage());
    }
}

// ============================================================================
// 8. MODAL AND FORM WORKFLOW TESTS
// ============================================================================

echo "\n=== UI/UX WORKFLOW TESTS ===\n";

// Test 8.1: Books management page contains modal
$response = http_get(BASE_URL . '/admin/books-management.php');
if ($response['code'] == 200) {
    $has_modal = strpos($response['body'], 'modal') !== false || strpos($response['body'], 'addBookModal') !== false;
    log_test('frontend', 'Books page contains modal elements', $has_modal);
}

// Test 8.2: Footfall scanner page loads
$response = http_get(BASE_URL . '/footfall/scanner.php');
if (in_array($response['code'], [200, 302])) {
    $is_loaded = strlen($response['body']) > 500;
    log_test('frontend', 'Footfall scanner page content loads', $is_loaded, "Size: " . strlen($response['body']) . " bytes");
}

// ============================================================================
// GENERATE TEST REPORT
// ============================================================================

echo "\n=== GENERATING TEST REPORT ===\n";

$report = "# WIET Library - End-to-End Test Report\n\n";
$report .= "**Date:** " . date('Y-m-d H:i:s') . "\n";
$report .= "**Duration:** " . round(microtime(true) - $start_time, 2) . " seconds\n\n";

$report .= "## TEST SUMMARY\n\n";
$report .= "| Metric | Count |\n";
$report .= "|--------|-------|\n";
$report .= "| **Total Tests** | {$total_tests} |\n";
$report .= "| **Passed** | {$passed_tests} ✅ |\n";
$report .= "| **Failed** | {$failed_tests} ❌ |\n";
$report .= "| **Pass Rate** | " . round(($passed_tests / $total_tests * 100), 1) . "% |\n\n";

foreach ($test_results as $category => $tests) {
    if (empty($tests)) continue;
    
    $report .= "## {$category} Tests\n\n";
    
    foreach ($tests as $test) {
        $status_icon = $test['status'] === 'PASS' ? '✅' : '❌';
        $report .= "- {$status_icon} **{$test['name']}** - {$test['status']}\n";
        if ($test['details']) {
            $report .= "  - Details: {$test['details']}\n";
        }
    }
    
    $report .= "\n";
}

$report .= "## SYSTEM STATUS\n\n";
$report .= "- **Web Server**: " . (function_exists('apache_get_version') ? 'Apache' : 'Unknown') . "\n";
$report .= "- **PHP Version**: " . phpversion() . "\n";
$report .= "- **Database**: " . (isset($pdo) ? 'Connected ✅' : 'Failed ❌') . "\n";
$report .= "- **Base URL**: " . BASE_URL . "\n\n";

// Save report
file_put_contents(REPORT_FILE, $report);
echo "Report saved to: " . REPORT_FILE . "\n";

echo "\n=== TEST EXECUTION COMPLETE ===\n";
echo "Total Tests: {$total_tests}\n";
echo "Passed: {$passed_tests} ✅\n";
echo "Failed: {$failed_tests} ❌\n";
echo "Pass Rate: " . round(($passed_tests / $total_tests * 100), 1) . "%\n";

?>
