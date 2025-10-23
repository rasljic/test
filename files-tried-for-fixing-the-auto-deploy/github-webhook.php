<?php
// GitHub Webhook Handler - Creates deployment trigger for cron
define('SECRET_TOKEN', '111222333444555'); // Change this! TODO: Also, move this to the .env file or other secure location !!!!!!!!!!!!!!!

$log_file = '/home/milanr/logs/github-webhook.log';
$deploy_branch = 'staging';

function logMessage($message)
{
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Get headers and payload
$headers = getallheaders();
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Check if payload is valid
if (!$data) {
    logMessage("Invalid payload");
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Invalid payload']));
}

// Get branch name
$ref = $data['ref'] ?? '';
$branch = str_replace('refs/heads/', '', $ref);
$github_event = $headers['X-Github-Event'] ?? 'unknown';

logMessage("Received $github_event for branch: $branch");

// Check if it's a push to staging branch
if ($github_event === 'push' && $branch === $deploy_branch) {
    // Create a trigger file for deployment
    $trigger_file = '/home/milanr/logs/deploy-now.txt';
    $trigger_data = json_encode([
        'branch' => $branch,
        'timestamp' => time(),
        'commit' => $data['after'] ?? '',
        'pusher' => $data['pusher']['name'] ?? 'unknown',
        'message' => $data['head_commit']['message'] ?? ''
    ]);

    file_put_contents($trigger_file, $trigger_data);
    logMessage("Deployment trigger created for commit: " . $data['after']);

    // Success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Deployment triggered',
        'branch' => $branch
    ]);
} else {
    logMessage("Skipped - Event: $github_event, Branch: $branch");
    http_response_code(200);
    echo json_encode([
        'status' => 'skipped',
        'message' => 'Not staging branch'
    ]);
}
