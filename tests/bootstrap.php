<?php

const WP_CONTENT_DIR = __DIR__;

$bootstrapConfig = require __DIR__ . '/bootstrapConfig.php';

if (!file_exists($bootstrapConfig['workflow']['config']) && !file_exists($bootstrapConfig['local']['config'])) {
    die('wp-tests-config.php not found');
}

if (file_exists($bootstrapConfig['workflow']['config'])) {
    $currentBootstrapConfig = $bootstrapConfig['workflow'];
} else {
    $currentBootstrapConfig = $bootstrapConfig['local'];
}

define('WP_TESTS_CONFIG_FILE_PATH', $currentBootstrapConfig['config']);

require_once WP_TESTS_CONFIG_FILE_PATH;
require_once $currentBootstrapConfig['functions'];

tests_add_filter('muplugins_loaded', static function () {
    require_once __DIR__ . '/../give.php';
});

tests_add_filter('setup_theme', static function () {
    echo 'Installing GiveWP.....' . PHP_EOL;
    give()->install();
});

require_once $currentBootstrapConfig['bootstrap'];

// Test cases
require_once __DIR__ . '/includes/framework/class-give-unit-test-case.php';

// Helpers
require_once __DIR__ . '/includes/framework/helpers/shims.php';
require_once __DIR__ . '/includes/framework/helpers/class-helper-form.php';
require_once __DIR__ . '/includes/framework/helpers/class-helper-payment.php';

