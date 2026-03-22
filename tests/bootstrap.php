<?php
/**
 * PHPUnit Bootstrap File for Mock WordPress Tests
 * 
 * @package ksfraser\MockWordPress
 */

// Find composer autoloader
$autoloader_paths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
];

$loaded = false;
foreach ($autoloader_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    echo "Error: Could not find Composer autoloader\n";
    exit(1);
}

// Define test constants if not already defined
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', __DIR__ . '/tests/fixtures/wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
