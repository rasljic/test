<?php
// Simple webhook tester
$log_file = '/home/milanr/webhook-test.log';
$timestamp = date('Y-m-d H:i:s');

// Log everything
$data = [
    'timestamp' => $timestamp,
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'raw_body' => file_get_contents('php://input'),
    'parsed_body' => json_decode(file_get_contents('php://input'), true)
];

file_put_contents($log_file, json_encode($data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Always return success
http_response_code(200);
echo json_encode(['status' => 'logged']);
?>
