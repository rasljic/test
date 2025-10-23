<?php
// Auto-deployment webhook that works with hosting restrictions
// This file will be in /home/milanr/public_html/auto-deploy.php after deployment

// Configuration
$deploy_branch = 'staging';
$log_file = dirname(__FILE__) . '/deploy-log.txt'; // Log in public_html
$trigger_file = dirname(__FILE__) . '/deploy-trigger.json'; // Trigger in public_html

// Simple logging function
function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    @file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Get the GitHub payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

writeLog("Webhook received");

// Validate payload
if (!$data || !isset($data['ref'])) {
    writeLog("Invalid payload");
    http_response_code(400);
    die(json_encode(['error' => 'Invalid payload']));
}

// Extract branch name
$ref = $data['ref'];
$branch = str_replace('refs/heads/', '', $ref);

writeLog("Push received for branch: $branch");

// Check if it's the deployment branch
if ($branch === $deploy_branch) {
    // Create trigger file in public_html (we know this works)
    $trigger_data = [
        'branch' => $branch,
        'timestamp' => time(),
        'date' => date('Y-m-d H:i:s'),
        'commit' => $data['after'] ?? 'unknown',
        'pusher' => $data['pusher']['name'] ?? 'unknown',
        'message' => $data['head_commit']['message'] ?? 'No message'
    ];

    $result = file_put_contents($trigger_file, json_encode($trigger_data, JSON_PRETTY_PRINT));

    if ($result !== false) {
        writeLog("Deployment trigger created successfully");
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Deployment triggered']);
    } else {
        writeLog("Failed to create trigger file");
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Could not create trigger']);
    }
} else {
    writeLog("Skipped - not deployment branch");
    http_response_code(200);
    echo json_encode(['status' => 'skipped', 'branch' => $branch]);
}
?>
