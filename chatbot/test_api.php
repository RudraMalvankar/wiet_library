<?php
// Quick test harness for chatbot API (will simulate session if needed)
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

// Optionally set a test student (if not using session auth)
// $_SESSION['student_id'] = 1;
// $_SESSION['member_no'] = 2511;
// $_SESSION['logged_in'] = true;

function call($action, $params=[]) {
    $url = 'http://localhost/wiet_lib/chatbot/api/bot.php?action=' . urlencode($action);
    foreach ($params as $k => $v) $url .= '&' . urlencode($k) . '=' . urlencode($v);
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "Cookie: PHPSESSID=" . session_id() . "\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $res = file_get_contents($url, false, $context);
    return $res;
}

header('Content-Type: text/plain');

echo "Testing chatbot API as current session (PHPSESSID=" . session_id() . ")\n\n";

echo "== my_loans ==\n";
echo call('my_loans');

echo "\n== visit_count ==\n";
echo call('visit_count');

echo "\n== search_books?q=data ==\n";
echo call('search_books', ['q' => 'data']);

?>