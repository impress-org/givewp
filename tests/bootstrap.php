<?php

use GiveTests\TestEnvironment;

require __DIR__ . '/../vendor/autoload.php';

const WP_CONTENT_DIR = __DIR__;

$testEnvironment = new TestEnvironment();

if (!$testEnvironment->hasConfig()) {
    die('wp-tests-config.php not found');
}

$currentTestEnvironment = $testEnvironment->current();

define('WP_TESTS_CONFIG_FILE_PATH', $currentTestEnvironment->config());

require_once WP_TESTS_CONFIG_FILE_PATH;
require_once $currentTestEnvironment->functions();

tests_add_filter('muplugins_loaded', static function () {
    require_once __DIR__ . '/../give.php';
});

tests_add_filter('setup_theme', static function () {
    echo 'Installing GiveWP.....' . PHP_EOL;
    give()->install();
});

require_once $currentTestEnvironment->bootstrap();

// Include legacy test case
require_once __DIR__ . '/includes/legacy/framework/class-give-unit-test-case.php';

// Include Helpers
require_once __DIR__ . '/includes/legacy/framework/helpers/shims.php';
require_once __DIR__ . '/includes/legacy/framework/helpers/class-helper-form.php';
require_once __DIR__ . '/includes/legacy/framework/helpers/class-helper-payment.php';

