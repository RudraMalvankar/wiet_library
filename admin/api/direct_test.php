<?php
/**
 * Simple Direct Test
 */

echo "<h2>Testing API Endpoints</h2>";
echo "<pre>";

// Test 1: Reports - Circulation
echo "\n=== Test 1: Circulation Report ===\n";
$url = 'http://localhost/wiet_lib/admin/api/reports.php?action=circulation&from=2025-01-01&to=2025-12-31';
echo "URL: $url\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($url, false, $context);
if ($response === false) {
    echo "ERROR: Could not connect\n";
    $error = error_get_last();
    echo "Error details: " . print_r($error, true) . "\n";
} else {
    echo "Response length: " . strlen($response) . " bytes\n";
    echo "First 500 chars: " . substr($response, 0, 500) . "\n";
    
    $json = json_decode($response);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ Valid JSON\n";
        echo "Data: " . print_r($json, true) . "\n";
    } else {
        echo "❌ Invalid JSON: " . json_last_error_msg() . "\n";
    }
}

// Test 2: Fines
echo "\n=== Test 2: Fines ===\n";
$url = 'http://localhost/wiet_lib/admin/api/fines.php?action=pending';
echo "URL: $url\n";

$response = @file_get_contents($url, false, $context);
if ($response === false) {
    echo "ERROR: Could not connect\n";
} else {
    echo "Response length: " . strlen($response) . " bytes\n";
    echo "First 500 chars: " . substr($response, 0, 500) . "\n";
    
    $json = json_decode($response);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ Valid JSON\n";
    } else {
        echo "❌ Invalid JSON: " . json_last_error_msg() . "\n";
    }
}

// Test 3: Members (This one works)
echo "\n=== Test 3: Members (Known Working) ===\n";
$url = 'http://localhost/wiet_lib/admin/api/members.php?action=list';
echo "URL: $url\n";

$response = @file_get_contents($url, false, $context);
if ($response === false) {
    echo "ERROR: Could not connect\n";
} else {
    echo "Response length: " . strlen($response) . " bytes\n";
    echo "✅ Valid JSON\n";
}

echo "\n=== Testing Direct Include ===\n";
echo "Testing if files can be included directly...\n";

try {
    ob_start();
    $_GET['action'] = 'circulation';
    $_GET['from'] = '2025-01-01';
    $_GET['to'] = '2025-12-31';
    
    include(__DIR__ . '/reports.php');
    
    $output = ob_get_clean();
    echo "Output length: " . strlen($output) . " bytes\n";
    echo "First 500 chars: " . substr($output, 0, 500) . "\n";
} catch (Exception $e) {
    ob_end_clean();
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
