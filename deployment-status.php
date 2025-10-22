<?php
// Deployment status checker
$log_file = '/home/milanr/github-webhook.log';

echo "<h1>Deployment Status</h1>";

if (file_exists($log_file)) {
    $last_lines = array_slice(file($log_file), -20);
    echo "<h2>Last 20 Deployment Log Entries:</h2>";
    echo "<pre>" . htmlspecialchars(implode('', $last_lines)) . "</pre>";
} else {
    echo "<p>No deployment log found yet.</p>";
}

// Show last commit in repository
$repo_dir = '/home/milanr/repositories/test';
if (is_dir($repo_dir)) {
    chdir($repo_dir);
    $last_commit = shell_exec('git log -1 --pretty=format:"%h - %an, %ar : %s"');
    echo "<h2>Last Commit in Repository:</h2>";
    echo "<pre>$last_commit</pre>";

    $current_branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));
    echo "<h2>Current Branch:</h2>";
    echo "<pre>$current_branch</pre>";
}
?>
