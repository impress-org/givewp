<?php

const WP_CONTENT_DIR = __DIR__;

$testConfig = require __DIR__ . '/config.php';

if (!file_exists($testConfig['workflow']['config']) && !file_exists($testConfig['local']['config'])) {
    die('wp-tests-config.php not found');
}

if (file_exists($testConfig['workflow']['config'])) {
    $config = $testConfig['workflow'];
} else {
    $config = $testConfig['local'];
}

define('WP_TESTS_CONFIG_FILE_PATH', $config['config']);

require_once WP_TESTS_CONFIG_FILE_PATH;
require_once __DIR__ . $config['functions'];

tests_add_filter('muplugins_loaded', static function () {
    require_once __DIR__ . '/../give.php';
});

tests_add_filter('setup_theme', static function () {
    echo 'Installing GiveWP.....' . PHP_EOL;
    give()->install();
});

require_once __DIR__ . $config['bootstrap'];

// Test cases
require_once __DIR__ . '/includes/framework/class-give-unit-test-case.php';

// Helpers
require_once __DIR__ . '/includes/framework/helpers/shims.php';
require_once __DIR__ . '/includes/framework/helpers/class-helper-form.php';
require_once __DIR__ . '/includes/framework/helpers/class-helper-payment.php';

