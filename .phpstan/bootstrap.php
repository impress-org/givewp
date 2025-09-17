<?php

// Minimal bootstrap for PHPStan to avoid executing the plugin runtime.

// Ensure Composer autoloading is available for classes under src/ and .phpstan/ extensions.
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Define ABSPATH if not running within a full WordPress environment.
if (!defined('ABSPATH')) {
    $root = dirname(__DIR__, 3);
    // Fallback to current working directory if expected structure isn't present.
    if (!is_dir($root)) {
        $root = getcwd();
    }
    define('ABSPATH', rtrim($root, "/\\") . '/');
}

// Avoid any accidental execution of plugin bootstrap.
define('GIVE_PHPSTAN_BOOTSTRAP', true);


