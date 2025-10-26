<?php
/**
 * Database Connection Test
 * Tests all API endpoints for JSON validity
 */

header('Content-Type: application/json');

$baseUrl = 'http://localhost/wiet_lib/admin/api/';
$results = [];

// Test endpoints
$tests = [
    'reports.php?action=circulation&from=2025-01-01&to=2025-12-31',
    'reports.php?action=financial&from=2025-01-01&to=2025-12-31',
    'reports.php?action=inventory&type=summary',
    'reports.php?action=members&type=summary',
    'fines.php?action=pending',
    'members.php?action=list',
];

foreach ($tests as $endpoint) {
    $url = $baseUrl . $endpoint;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        $error = error_get_last();
        $results[] = [
            'endpoint' => $endpoint,
            'status' => 'ERROR',
            'message' => 'Failed to connect',
            'error_detail' => $error['message'] ?? 'Unknown error',
            'url' => $url
        ];
        continue;
    }
    
    $json = json_decode($response);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        $results[] = [
            'endpoint' => $endpoint,
            'status' => 'OK',
            'message' => 'Valid JSON response'
        ];
    } else {
        $results[] = [
            'endpoint' => $endpoint,
            'status' => 'INVALID JSON',
            'message' => json_last_error_msg(),
            'response_preview' => substr($response, 0, 200)
        ];
    }
}

echo json_encode([
    'test_time' => date('Y-m-d H:i:s'),
    'total_tests' => count($tests),
    'results' => $results
], JSON_PRETTY_PRINT);
?>
