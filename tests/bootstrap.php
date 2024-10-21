<?php

use Give\Tests\Framework\TestHooks;
use Give\Tests\TestEnvironment;

require __DIR__ . '/../vendor/autoload.php';

const WP_CONTENT_DIR = __DIR__;

$testEnvironment = new TestEnvironment();

// check if a required `wp-tests-config.php` is present
if (!$testEnvironment->hasConfig()) {
    die('wp-tests-config.php not found');
}

// get the current test environment (Local or Workflow)
$currentTestEnvironment = $testEnvironment->current();

// define for use in WP bootstrap file
define('WP_TESTS_CONFIG_FILE_PATH', $currentTestEnvironment->config());

// temporary while event tickets is in beta
if (!defined('GIVE_FEATURE_ENABLE_EVENT_TICKETS')){
    define('GIVE_FEATURE_ENABLE_EVENT_TICKETS', true);
}

// load GiveWP
TestHooks::addFilter('muplugins_loaded', static function () {
    require_once __DIR__ . '/../give.php';
});

// install GiveWP
TestHooks::addFilter('setup_theme', static function () {
    echo 'Installing GiveWP.....' . PHP_EOL;
    give()->install();
});

// pull in WP bootstrap file which looks for WP_TESTS_CONFIG_FILE_PATH defined above
require_once $currentTestEnvironment->bootstrap();

// Include legacy test case
require_once __DIR__ . '/includes/legacy/framework/class-give-unit-test-case.php';

// Include legacy helpers
require_once __DIR__ . '/includes/legacy/framework/helpers/shims.php';
require_once __DIR__ . '/includes/legacy/framework/helpers/class-helper-form.php';
require_once __DIR__ . '/includes/legacy/framework/helpers/class-helper-payment.php';
