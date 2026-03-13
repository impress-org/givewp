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

// Register high-version test stubs for Uplink global functions so tests can control
// stellarwp_uplink_is_product_license_active() and stellarwp_uplink_is_feature_available()
// without spinning up real Uplink licensing logic.
//
// Must be hooked to 'init' (which fires before 'wp_loaded') so the write gate inside
// _stellarwp_uplink_global_function_registry() is still open. Version '999.0.0-give-tests'
// is higher than any real Uplink release, so these stubs win the leader election.
TestHooks::addFilter('init', static function () {
    _stellarwp_uplink_instance_registry('999.0.0-give-tests');

    _stellarwp_uplink_global_function_registry(
        'stellarwp_uplink_is_product_license_active',
        '999.0.0-give-tests',
        static function (string $product): bool {
            return \Give\Tests\Unit\VendorOverrides\Uplink\UplinkStubs::$productActive;
        }
    );

    _stellarwp_uplink_global_function_registry(
        'stellarwp_uplink_is_feature_available',
        '999.0.0-give-tests',
        static function (string $slug): bool {
            return in_array($slug, \Give\Tests\Unit\VendorOverrides\Uplink\UplinkStubs::$availableFeatures, true);
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
