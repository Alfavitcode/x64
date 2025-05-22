<?php
/**
 * Pre-Deployment Hook
 * This script runs before FTP deployment to prepare files
 * 
 * You can customize this script to:
 * - Minify CSS/JS
 * - Generate sitemap
 * - Update version numbers
 * - Generate configuration files
 * - Etc.
 */

echo "Running pre-deployment hook...\n";

// Get deployment environment
$env = getenv('DEPLOY_ENV') ?: 'production';
echo "Deployment environment: $env\n";

// Create timestamp for cache busting
$timestamp = date('YmdHis');
echo "Build timestamp: $timestamp\n";

// Function to recursively find files
function findFiles($dir, $pattern) {
    $files = [];
    $dir_handle = opendir($dir);
    
    while ($file = readdir($dir_handle)) {
        if ($file == '.' || $file == '..') continue;
        
        $filepath = $dir . '/' . $file;
        
        if (is_dir($filepath)) {
            $subdirFiles = findFiles($filepath, $pattern);
            $files = array_merge($files, $subdirFiles);
        } else if (preg_match($pattern, $file)) {
            $files[] = $filepath;
        }
    }
    
    closedir($dir_handle);
    return $files;
}

// Example: Update version.php file with build info
if (file_exists('includes/version.php')) {
    $content = file_get_contents('includes/version.php');
    $content = preg_replace(
        '/\$build_timestamp\s*=\s*[\'"][^\'"]*[\'"]/i', 
        "\$build_timestamp = '$timestamp'", 
        $content
    );
    file_put_contents('includes/version.php', $content);
    echo "Updated build timestamp in version.php\n";
}

// Example: Rename environment-specific config file
if ($env === 'production' && file_exists("includes/config.$env.php")) {
    copy("includes/config.$env.php", "includes/config.php");
    echo "Copied environment-specific config file\n";
}

// Example: Create a build info file
$buildInfo = [
    'timestamp' => $timestamp,
    'environment' => $env,
    'git_hash' => trim(shell_exec('git rev-parse HEAD')),
    'build_date' => date('Y-m-d H:i:s'),
];

file_put_contents('build-info.php', "<?php\n/* Auto-generated build info */\nreturn " . var_export($buildInfo, true) . ";\n");
echo "Created build-info.php\n";

echo "Pre-deployment hook completed.\n";
?> 