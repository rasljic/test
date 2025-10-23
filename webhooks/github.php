<?php

// Simple webhook handler for GitHub
$webhook_secret = ''; // Leave empty for now
$repo_path = '/home/milanr/repositories/test';
$branch = 'staging';
$deploy_path = '/home/milanr/public_html';

// Get payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Check if it's the right branch
if ($data['ref'] === 'refs/heads/' . $branch) {
    // Pull latest changes
    exec("cd $repo_path && git fetch origin && git reset --hard origin/$branch 2>&1", $output);

    // Deploy files
    exec("rsync -av --delete --exclude='.git' $repo_path/ $deploy_path/ 2>&1", $output);

    // Log the deployment
    file_put_contents('/home/milanr/logs/deploy.log',
        date('Y-m-d H:i:s') . " - Deployment triggered\n" . implode("\n", $output) . "\n\n",
        FILE_APPEND);

    echo "Deployment successful";
} else {
    echo "Not the target branch";
}
