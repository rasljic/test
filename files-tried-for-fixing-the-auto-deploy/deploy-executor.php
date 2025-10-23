#!/usr/local/bin/php
<?php
// Deployment executor - FIXED VERSION that preserves cPanel files
// This should be saved as /home/milanr/deploy-executor.php

$trigger_file = '/home/milanr/public_html/deploy-trigger.json';
$log_file = '/home/milanr/logs/deploy-executor.log';
$repo_dir = '/home/milanr/repositories/test';
$deploy_dir = '/home/milanr/public_html';

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
    'git pull origin staging'
];

foreach ($commands as $cmd) {
    $output = runCmd($cmd);
    file_put_contents($log_file, "$ $cmd\n$output\n", FILE_APPEND);
}

// Deploy files - IMPORTANT: Don't use --delete flag!
file_put_contents($log_file, "Deploying files to $deploy_dir\n", FILE_APPEND);

// Use rsync WITHOUT --delete to preserve cPanel files
$rsync_cmd = "rsync -av " .
    "--exclude='.git' " .
    "--exclude='.gitignore' " .
    "--exclude='deploy-executor.php' " .
    "--exclude='*.log' " .
    "--exclude='.well-known' " .
    "--exclude='cgi-bin' " .
//    "--exclude='.htaccess' " .  // Don't overwrite .htaccess
    "--exclude='error_log' " .
    "$repo_dir/ $deploy_dir/";

$output = runCmd($rsync_cmd);
file_put_contents($log_file, "$ $rsync_cmd\n$output\n", FILE_APPEND);

// If rsync doesn't work, use cp
if (strpos($output, 'command not found') !== false) {
    file_put_contents($log_file, "Rsync not available, using cp instead\n", FILE_APPEND);

    // Get list of files to copy (excluding system files)
    $files_to_copy = scandir($repo_dir);
    $exclude = ['.', '..', '.git', '.gitignore', 'deploy-executor.php', '.well-known', 'cgi-bin'];

    foreach ($files_to_copy as $file) {
        if (!in_array($file, $exclude)) {
            $src = "$repo_dir/$file";
            $dst = "$deploy_dir/$file";

            if (is_dir($src)) {
                $cp_cmd = "cp -r $src $dst 2>&1";
            } else {
                $cp_cmd = "cp $src $dst 2>&1";
            }

            $output = runCmd($cp_cmd);
            if ($output) {
                file_put_contents($log_file, "Copied: $file\n", FILE_APPEND);
            }
        }
    }
}

// Verify deployment
if (file_exists($deploy_dir . '/index.php')) {
    $repo_index_time = filemtime($repo_dir . '/index.php');
    $deploy_index_time = filemtime($deploy_dir . '/index.php');

    if ($repo_index_time <= $deploy_index_time) {
        file_put_contents($log_file, "✓ Deployment verified - index.php is up to date\n", FILE_APPEND);
    } else {
        file_put_contents($log_file, "⚠ Warning - index.php might not be updated\n", FILE_APPEND);
    }
}

// Ensure proper permissions
runCmd("chmod 755 $deploy_dir");
runCmd("find $deploy_dir -type f -name '*.php' -exec chmod 644 {} \;");

file_put_contents($log_file, date('[Y-m-d H:i:s]') . " Deployment completed\n", FILE_APPEND);
echo "Deployment completed\n";
?>
