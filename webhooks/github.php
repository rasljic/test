<?php
//
//// Simple webhook handler for GitHub
//$webhook_secret = ''; // Leave empty for now
//$repo_path = '/home/milanr/repositories/test';
//$branch = 'staging';
//$deploy_path = '/home/milanr/public_html';
//
//// Get payload
//$payload = file_get_contents('php://input');
//$data = json_decode($payload, true);
//
//// Check if it's the right branch
//if ($data['ref'] === 'refs/heads/' . $branch) {
//    // Pull latest changes
//    exec("cd $repo_path && git fetch origin && git reset --hard origin/$branch 2>&1", $output);
//
//    // Deploy files
//    exec("rsync -av --delete --exclude='.git' $repo_path/ $deploy_path/ 2>&1", $output);
//
//    // Log the deployment
//    if (!empty($output)) {
//        file_put_contents('/home/milanr/logs/deploy.log',
//            date('Y-m-d H:i:s') . " - Deployment triggered\n" . implode("\n", $output) . "\n\n",
//            FILE_APPEND);
//    } else {
//        file_put_contents('/home/milanr/logs/deploy.log',
//            date('Y-m-d H:i:s') . " - Deployment triggered but no output (exec likely disabled)\n\n",
//            FILE_APPEND);
//    }
//
//    echo "Deployment successful";
//} else {
//    echo "Not the target branch";
//}





//
//// Simple webhook handler for GitHub
//$webhook_secret = ''; // Leave empty for now
//$repo_path = '/home/milanr/repositories/test';
//$branch = 'staging';
//$deploy_path = '/home/milanr/public_html';
//$log_path = '/home/milanr/logs/deploy.log';
//// Get payload
//$payload = file_get_contents('php://input');
//$data = json_decode($payload, true);
//// Check if it's the right branch
//if ($data['ref'] === 'refs/heads/' . $branch) {
//    // Fetch latest changes
//    exec("cd $repo_path && git fetch origin 2>&1", $output);
//    // Compare local and remote commit hashes
//    $local_hash = trim(shell_exec("cd $repo_path && git rev-parse HEAD"));
//    $remote_hash = trim(shell_exec("cd $repo_path && git rev-parse origin/$branch"));
//    if ($local_hash !== $remote_hash) {
//        // Pull latest changes
//        exec("cd $repo_path && git reset --hard origin/$branch 2>&1", $output);
//        // Deploy files
//        exec("rsync -av --delete --exclude='.git' $repo_path/ $deploy_path/ 2>&1", $output);
//        // Ensure public_html stays at 755
//        exec("chmod 755 $deploy_path");
//        exec("find $deploy_path -type d -exec chmod 755 {} \\;");
//        exec("find $deploy_path -type f -exec chmod 644 {} \\;");
//
//        // Log the deployment
//        if (!empty($output)) {
//            file_put_contents($log_path,
//                date('Y-m-d H:i:s') . " - Deployment triggered\n" . implode("\n", $output) . "\n\n",
//                FILE_APPEND);
//        } else {
//            file_put_contents('/home/milanr/logs/deploy.log',
//                date('Y-m-d H:i:s') . " - Deployment triggered but no output (exec likely disabled)\n\n",
//                FILE_APPEND);
//        }
//
//        echo "Deployment successful";
//    } else {
//        // Log skipped deployment
//        file_put_contents($log_path,
//            date('Y-m-d H:i:s') . " - No changes detected. Deployment skipped.\n\n",
//            FILE_APPEND);
//        echo "No changes detected";
//    }
//} else {
//    echo "Not the target branch";
//}




//// Simple webhook handler for GitHub
//$webhook_secret = ''; // Leave empty for now
//$repo_path = '/home/milanr/repositories/test';
//$branch = 'staging';
//$deploy_path = '/home/milanr/public_html';
//$log_path = '/home/milanr/logs/deploy.log';
//
//// Get payload
//$payload = file_get_contents('php://input');
//$data = json_decode($payload, true);
//
//// Check if it's the right branch
//if ($data['ref'] === 'refs/heads/' . $branch) {
//    // Get current local and remote hashes BEFORE fetching
//    $local_hash = trim(shell_exec("cd $repo_path && git rev-parse HEAD"));
//    $remote_hash = trim(shell_exec("cd $repo_path && git ls-remote origin refs/heads/$branch | cut -f1"));
//
//    if ($local_hash !== $remote_hash) {
//        // Fetch and reset only if hashes differ
//        exec("cd $repo_path && git fetch origin && git reset --hard origin/$branch 2>&1", $output);
//
//        // Deploy files
//        exec("rsync -av --delete --exclude='.git' $repo_path/ $deploy_path/ 2>&1", $output);
//
//        // Ensure public_html stays at 755
//        exec("chmod 755 $deploy_path");
//        exec("find $deploy_path -type d -exec chmod 755 {} \\;");
//        exec("find $deploy_path -type f -exec chmod 644 {} \\;");
//
//        // Log the deployment
//        if (!empty($output)) {
//            file_put_contents($log_path,
//                date('Y-m-d H:i:s') . " - Deployment triggered\n" . implode("\n", $output) . "\n\n",
//                FILE_APPEND);
//        } else {
//            file_put_contents('/home/milanr/logs/deploy.log',
//                date('Y-m-d H:i:s') . " - Deployment triggered but no output (exec likely disabled)\n\n",
//                FILE_APPEND);
//        }
//
//        echo "Deployment successful";
//    } else {
//        // Log skipped deployment
//        file_put_contents($log_path,
//            date('Y-m-d H:i:s') . " - No changes detected. Deployment skipped.\n\n",
//            FILE_APPEND);
//
//        echo "No changes detected";
//    }
//} else {
//    echo "Not the target branch";
//}





// Simple webhook handler for GitHub
$webhook_secret = ''; // Leave empty for now
$repo_path = '/home/milanr/repositories/test';
$branch = 'staging';
$deploy_path = '/home/milanr/public_html';
$log_path = '/home/milanr/logs/deploy.log';

// Get payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Check if webhook triggered
if (!empty($data['ref']) && $data['ref'] === 'refs/heads/' . $branch) {
    // Deployment logic:

    // Check if it's the right branch
//    if ($data['ref'] === 'refs/heads/' . $branch) {
        // Get current local and remote hashes BEFORE fetching
        $local_hash = trim(shell_exec("cd $repo_path && git rev-parse HEAD"));
        $remote_hash = trim(shell_exec("cd $repo_path && git ls-remote origin refs/heads/$branch | cut -f1"));

        if ($local_hash !== $remote_hash) {
            // Fetch and reset only if hashes differ
            exec("cd $repo_path && git fetch origin && git reset --hard origin/$branch 2>&1", $output);

            // Deploy files
            exec("rsync -av --delete --exclude='.git' $repo_path/ $deploy_path/ 2>&1", $output);

            // Ensure public_html stays at 755
            exec("chmod 755 $deploy_path");
            exec("find $deploy_path -type d -exec chmod 755 {} \\;");
            exec("find $deploy_path -type f -exec chmod 644 {} \\;");

            // Log the deployment
            if (!empty($output)) {
                file_put_contents($log_path,
                    date('Y-m-d H:i:s') . " - Deployment triggered\n" . implode("\n", $output) . "\n\n",
                    FILE_APPEND);
            } else {
                file_put_contents('/home/milanr/logs/deploy.log',
                    date('Y-m-d H:i:s') . " - Deployment triggered but no output (exec likely disabled)\n\n",
                    FILE_APPEND);
            }

            echo "Deployment successful";
        } else {
            // Log skipped deployment
            file_put_contents($log_path,
                date('Y-m-d H:i:s') . " - No changes detected. Deployment skipped.\n\n",
                FILE_APPEND);

            echo "No changes detected";
        }
//    } else {
//        echo "Not the target branch";
//    }

} else {
    // Log that script ran but no webhook was received
    file_put_contents($log_path,
        date('Y-m-d H:i:s') . " - Script ran but no webhook payload received.\n\n",
        FILE_APPEND);
}
