#!/usr/local/bin/php
<?php
// Deployment script to be executed by cron job
// This runs in CLI context where shell_exec might work

$trigger_file = '/home/milanr/logs/deploy-now.txt';
$log_file = '/home/milanr/logs/cron-deployment.log';
$repo_dir = '/home/milanr/repositories/test';

function logMsg($msg) {
    global $log_file;
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

// Check if deployment is needed
if (!file_exists($trigger_file)) {
    exit(0); // No deployment needed
}

// Read trigger data
$trigger_data = json_decode(file_get_contents($trigger_file), true);
logMsg("Starting deployment for: " . json_encode($trigger_data));

// Remove trigger file
unlink($trigger_file);

// Try to use exec or system if shell_exec is disabled
function runCommand($cmd) {
    $output = '';
    $return_var = 0;

    // Try different command execution methods
    if (function_exists('exec')) {
        exec($cmd . ' 2>&1', $output_array, $return_var);
        $output = implode("\n", $output_array);
    } elseif (function_exists('system')) {
        ob_start();
        system($cmd . ' 2>&1', $return_var);
        $output = ob_get_clean();
    } elseif (function_exists('passthru')) {
        ob_start();
        passthru($cmd . ' 2>&1', $return_var);
        $output = ob_get_clean();
    } elseif (function_exists('shell_exec')) {
        $output = shell_exec($cmd . ' 2>&1');
    } else {
        return "No command execution functions available";
    }

    return $output;
}

// Change to repository directory
chdir($repo_dir);
logMsg("Changed to directory: " . getcwd());

// Run git commands
$commands = [
    'git fetch origin',
    'git checkout staging',
    'git reset --hard origin/staging',
    'git pull origin staging'
];

foreach ($commands as $cmd) {
    logMsg("Running: $cmd");
    $output = runCommand($cmd);
    logMsg("Output: " . $output);
}

// Trigger cPanel deployment
logMsg("Running cPanel git-deploy");
$deploy_output = runCommand('/usr/local/cpanel/3rdparty/bin/git-deploy');
logMsg("Deploy output: " . $deploy_output);

logMsg("Deployment completed\n");
?>
```

### Step 3: Set Up Cron Job in cPanel

1. Go to **cPanel → Cron Jobs**
2. Add a new cron job:
- **Common Settings**: "Once Per Minute (* * * * *)"
- **Command**:
```
/usr/local/bin/php /home/milanr/run-deployment.php >/dev/null 2>&1
