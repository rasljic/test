<?php
// GitHub Webhook Handler for Auto-Deployment
// This file is part of the repository and will be deployed to public_html

// Security token (optional but recommended)
define('SECRET_TOKEN', '111222333444555'); // Change this! TODO: Also, move this to the .env file or other secure location !!!!!!!!!!!!!!!

// Configuration - paths relative to where this file will be deployed
$repo_dir = '/home/milanr/repositories/test';
$deploy_branch = 'staging';
$log_file = '/home/milanr/github-webhook.log';

// Function to log messages
function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Get headers and payload
$headers = getallheaders();
$payload = file_get_contents('php://input');

// Parse the payload first to check if it's valid
$data = json_decode($payload, true);

// Check if payload is valid
if (!$data) {
    logMessage("Invalid payload received");
    http_response_code(400);
    die('Invalid payload');
}

// Verify GitHub signature (optional but recommended)
if (!empty(SECRET_TOKEN) && isset($headers['X-Hub-Signature-256'])) {
    $hub_signature = $headers['X-Hub-Signature-256'] ?? '';
    $calculated_signature = 'sha256=' . hash_hmac('sha256', $payload, SECRET_TOKEN);

    if (!hash_equals($hub_signature, $calculated_signature)) {
        logMessage("Invalid signature");
        http_response_code(403);
        die('Unauthorized');
    }
}

// Get branch name from ref
$ref = $data['ref'] ?? '';
$branch = str_replace('refs/heads/', '', $ref);

logMessage("Received push for branch: $branch from " . ($data['pusher']['name'] ?? 'unknown'));

// Only deploy if it's the correct branch
if ($branch === $deploy_branch) {
    logMessage("Starting deployment for branch: $branch");

    // Change to repository directory
    chdir($repo_dir);

    // Git commands to execute
    $commands = [
        'git fetch origin 2>&1',
        "git checkout $deploy_branch 2>&1",
        "git reset --hard origin/$deploy_branch 2>&1",
        'git pull origin ' . $deploy_branch . ' 2>&1'
    ];

    $output = [];
    foreach ($commands as $command) {
        $result = shell_exec($command);
        $output[] = "$ $command\n$result";
        logMessage("Executed: $command");
    }

    // Trigger cPanel deployment
    $deploy_command = '/usr/local/cpanel/3rdparty/bin/git-deploy 2>&1';
    $deploy_result = shell_exec($deploy_command);
    $output[] = "$ $deploy_command\n$deploy_result";

    logMessage("Deployment completed successfully");

    // Send success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Deployment completed',
        'branch' => $branch,
        'timestamp' => date('c')
    ]);

} else {
    logMessage("Skipped - not the deployment branch (received: $branch, expected: $deploy_branch)");
    http_response_code(200);
    echo json_encode([
        'status' => 'skipped',
        'message' => "Not the deployment branch",
        'received_branch' => $branch,
        'expected_branch' => $deploy_branch
    ]);
}
?>
