<?php
// Minimal webhook debug - this will tell us if the file is being hit at all
error_reporting(E_ALL);
ini_set('display_errors', 1);

$debug_file = '/home/milanr/logs/webhook-debug.txt';

// Log absolutely everything
$debug_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'headers' => getallheaders(),
    'get_params' => $_GET,
    'post_raw' => file_get_contents('php://input'),
    'files_created' => []
];

// Try to create multiple test files to see what works
$test_locations = [
    '/home/milanr/logs/test1.txt' => 'Test 1',
    '/home/milanr/public_html/test2.txt' => 'Test 2',
    '/tmp/test3.txt' => 'Test 3',
];

foreach ($test_locations as $path => $content) {
    if (@file_put_contents($path, $content . ' - ' . date('Y-m-d H:i:s'))) {
        $debug_data['files_created'][] = $path;
    }
}

// Save debug data
file_put_contents($debug_file, json_encode($debug_data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Always return success
http_response_code(200);
echo json_encode(['status' => 'debug', 'message' => 'Debug data saved']);
?>
