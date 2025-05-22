<?php
/**
 * Version information
 * This file is automatically updated during deployment
 */

// Version components
$major_version = '1';
$minor_version = '0';
$patch_version = '0';
$build_timestamp = '20230101000000'; // Will be updated by pre-deployment hook

// Full version string
$version = "$major_version.$minor_version.$patch_version";

// Version with build
$version_with_build = "$version.$build_timestamp";

/**
 * Returns version information
 * 
 * @param bool $include_build Whether to include build information
 * @return string Version string
 */
function get_version($include_build = false) {
    global $version, $version_with_build;
    return $include_build ? $version_with_build : $version;
}

/**
 * Adds version as query string to asset URL for cache busting
 * 
 * @param string $url URL of the asset
 * @return string URL with version parameter
 */
function version_asset($url) {
    global $build_timestamp;
    $separator = strpos($url, '?') === false ? '?' : '&';
    return $url . $separator . 'v=' . $build_timestamp;
}
?> 