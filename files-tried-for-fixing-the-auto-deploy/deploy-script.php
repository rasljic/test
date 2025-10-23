#!/usr/bin/php
<?php
// Deployment script to be run via cron
// This runs with CLI PHP which usually has fewer restrictions

$deploy_request = '/home/milanr/deploy-request.json';
$log_file = '/home/milanr/deployment-cron.log';

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Check if there's a deployment request
if (!file_exists($deploy_request)) {
    exit(0); // No deployment needed
}

// Read and process the request
$request = json_decode(file_get_contents($deploy_request), true);
logMessage("Processing deployment request: " . json_encode($request));

// Delete the request file so we don't process it again
unlink($deploy_request);

// Change to repository directory
chdir('/home/milanr/repositories/test');

// Execute git commands
$commands = [
    'git fetch origin 2>&1',
    'git checkout staging 2>&1',
    'git reset --hard origin/staging 2>&1',
    'git pull origin staging 2>&1',
    '/usr/local/cpanel/3rdparty/bin/git-deploy 2>&1'
];

foreach ($commands as $command) {
    $output = shell_exec($command);
    logMessage("$ $command");
    logMessage($output);
}

logMessage("Deployment completed\n");
?>
