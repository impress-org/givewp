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

// Register high-version test stubs for Harbor global functions so tests can control
// lw_harbor_is_product_license_active() and lw_harbor_is_feature_available()
// without spinning up real Harbor licensing logic.
//
// Must be hooked to 'init' (which fires before 'wp_loaded') so the write gate inside
// _lw_harbor_global_function_registry() is still open. Version '999.0.0-give-tests'
// is higher than any real Harbor release, so these stubs win the leader election.
TestHooks::addFilter('init', static function () {
    _lw_harbor_instance_registry('999.0.0-give-tests');

    _lw_harbor_global_function_registry(
        'lw_harbor_is_product_license_active',
        '999.0.0-give-tests',
        static function (string $product): bool {
            return \Give\Tests\Unit\VendorOverrides\Harbor\HarborStubs::$productActive;
        }
    );

    _lw_harbor_global_function_registry(
        'lw_harbor_is_feature_available',
        '999.0.0-give-tests',
        static function (string $slug): bool {
            return in_array($slug, \Give\Tests\Unit\VendorOverrides\Harbor\HarborStubs::$availableFeatures, true);
        }
    );
}, 999);

// pull in WP bootstrap file which looks for WP_TESTS_CONFIG_FILE_PATH defined above
require_once $currentTestEnvironment->bootstrap();

// Include legacy test case
require_once __DIR__ . '/includes/legacy/framework/class-give-unit-test-case.php';

// Include legacy helpers
require_once __DIR__ . '/includes/legacy/framework/helpers/shims.php';
require_once __DIR__ . '/includes/legacy/framework/helpers/class-helper-form.php';
require_once __DIR__ . '/includes/legacy/framework/helpers/class-helper-payment.php';
