<?php
/**
 * Deployment Report Generator
 * Used by GitHub Actions to create a deployment summary
 */

$date = date('Y-m-d H:i:s');
$gitHash = trim(shell_exec('git rev-parse --short HEAD'));
$gitBranch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));
$totalFiles = (int)trim(shell_exec('git ls-files | wc -l'));

// List changed files in the last commit
$changedFiles = shell_exec('git diff-tree --no-commit-id --name-only -r HEAD');
$changedFilesArray = $changedFiles ? explode("\n", trim($changedFiles)) : [];

// Count file types
$fileTypes = [];
foreach ($changedFilesArray as $file) {
    if (empty($file)) continue;
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if (!isset($fileTypes[$ext])) {
        $fileTypes[$ext] = 0;
    }
    $fileTypes[$ext]++;
}

// Create report
echo "## 📊 Deployment Report\n\n";
echo "**Date:** $date\n\n";
echo "**Branch:** $gitBranch\n\n";
echo "**Commit:** $gitHash\n\n";
echo "**Total Files in Repository:** $totalFiles\n\n";

echo "### 🔄 Changed Files\n\n";
if (count($changedFilesArray) > 0) {
    echo "| File | Type |\n";
    echo "| ---- | ---- |\n";
    foreach ($changedFilesArray as $file) {
        if (empty($file)) continue;
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        echo "| $file | $ext |\n";
    }
} else {
    echo "No files changed in this deployment.\n";
}

echo "\n### 📊 File Types Summary\n\n";
if (count($fileTypes) > 0) {
    echo "| Extension | Count |\n";
    echo "| --------- | ----- |\n";
    foreach ($fileTypes as $ext => $count) {
        $ext = $ext ?: 'no extension';
        echo "| $ext | $count |\n";
    }
} else {
    echo "No files changed in this deployment.\n";
}
?> 