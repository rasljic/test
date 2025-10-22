<?php
// GitHub Webhook Handler for Auto-Deployment
// Version that works without shell_exec

// Security token (optional but recommended)
define('SECRET_TOKEN', '111222333444555'); // Change this! TODO: Also, move this to the .env file or other secure location !!!!!!!!!!!!!!!

// Configuration
$log_file = '/home/milanr/logs/github-webhook.log';
$deploy_branch = 'staging';

// Function to log messages
function logMessage($message)
{
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Get headers and payload
$headers = getallheaders();
$payload = file_get_contents('php://input');

logMessage("=== New webhook received ===");
logMessage("Headers: " . json_encode($headers));

// Parse the payload
$data = json_decode($payload, true);

// Check if payload is valid
if (!$data) {
    logMessage("Invalid payload received: " . substr($payload, 0, 500));
    http_response_code(400);
    die('Invalid payload');
}

// Log the GitHub event type
$github_event = $headers['X-Github-Event'] ?? 'unknown';
logMessage("GitHub Event: $github_event");

// Get branch name from ref
$ref = $data['ref'] ?? '';
$branch = str_replace('refs/heads/', '', $ref);

logMessage("Ref: $ref");
logMessage("Parsed branch: $branch");
logMessage("Pusher: " . ($data['pusher']['name'] ?? 'unknown'));

// Only deploy if it's a push event to the correct branch
if ($github_event === 'push' && $branch === $deploy_branch) {
    logMessage("Starting deployment for branch: $branch");

    // Since shell_exec is disabled, we'll trigger deployment via HTTP request
    // Option 1: Create a flag file that a cron job checks
    $flag_file = '/home/milanr/logs/deploy-flag.txt';
    file_put_contents($flag_file, date('Y-m-d H:i:s') . " - Deploy requested for branch: $branch\n");
    logMessage("Deploy flag file created");

    // Option 2: Try to use cPanel's Git deployment via HTTP
    // This creates a simple deployment request that will be handled by cron
    $deploy_request = '/home/milanr/logs/deploy-request.json';
    file_put_contents($deploy_request, json_encode([
        'branch' => $branch,
        'timestamp' => date('c'),
        'commit' => $data['after'] ?? 'unknown',
        'pusher' => $data['pusher']['name'] ?? 'unknown'
    ]));

    logMessage("Deployment request created");

    // Send success response to GitHub
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Deployment request received',
        'branch' => $branch,
        'timestamp' => date('c')
    ]);

} else {
    logMessage("Skipped - Event: $github_event, Branch: $branch (expected: $deploy_branch)");
    http_response_code(200);
    echo json_encode([
        'status' => 'skipped',
        'message' => "Not a push to deployment branch",
        'event' => $github_event,
        'received_branch' => $branch,
        'expected_branch' => $deploy_branch
    ]);
}

logMessage("=== Webhook processing complete ===\n");
