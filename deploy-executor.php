#!/usr/local/bin/php
<?php
// Deployment executor - runs via cron every minute
// This should be saved as /home/milanr/deploy-executor.php

$trigger_file = '/home/milanr/public_html/deploy-trigger.json';
$log_file = '/home/milanr/logs/deploy-executor.log';
$repo_dir = '/home/milanr/repositories/test';

// Check if trigger file exists
if (!file_exists($trigger_file)) {
    exit(0); // No deployment needed
}

// Read and delete trigger file
$trigger_data = json_decode(file_get_contents($trigger_file), true);
unlink($trigger_file);

// Log deployment start
file_put_contents($log_file, "\n" . date('[Y-m-d H:i:s]') . " Starting deployment\n", FILE_APPEND);
file_put_contents($log_file, "Trigger data: " . json_encode($trigger_data) . "\n", FILE_APPEND);

// Change to repository directory
chdir($repo_dir);

// Try different PHP execution functions
function runCmd($cmd) {
    $output = [];
    $return_var = -1;

    if (function_exists('exec')) {
        exec($cmd . ' 2>&1', $output, $return_var);
        return implode("\n", $output);
    } elseif (function_exists('shell_exec')) {
        return shell_exec($cmd . ' 2>&1');
    } elseif (function_exists('system')) {
        ob_start();
        system($cmd . ' 2>&1', $return_var);
        return ob_get_clean();
    } else {
        return "No execution functions available";
    }
}

// Execute git commands
$commands = [
    'pwd',
    'git fetch origin',
    'git checkout staging',
    'git pull origin staging',
    '/usr/local/cpanel/3rdparty/bin/git-deploy'
];

foreach ($commands as $cmd) {
    $output = runCmd($cmd);
    file_put_contents($log_file, "$ $cmd\n$output\n", FILE_APPEND);
}

file_put_contents($log_file, date('[Y-m-d H:i:s]') . " Deployment completed\n", FILE_APPEND);
echo "Deployment completed\n";
?>
