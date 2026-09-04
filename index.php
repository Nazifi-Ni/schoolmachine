<?php
// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Root index.php for Shared Hosting (InfinityFree)
 * 
 * This file serves as a simple passthrough to the actual application entry point.
 */

// Basic check to see if vendor folder exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("FATAL ERROR: The 'vendor' folder is missing! Please make sure you uploaded the entire 'vendor' folder to htdocs.");
}

// Return false if the requested file exists to allow the built-in server to serve it directly
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
        return false;
    }
}

require_once __DIR__ . '/public/index.php';
