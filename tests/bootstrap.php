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

// get the current test environment (wp-env, Local or Workflow)
$currentTestEnvironment = $testEnvironment->current();

// define for use in WP bootstrap file
define('WP_TESTS_CONFIG_FILE_PATH', $currentTestEnvironment->config());

// temporary while event tickets is in beta
if (!defined('GIVE_FEATURE_ENABLE_EVENT_TICKETS')){
    define('GIVE_FEATURE_ENABLE_EVENT_TICKETS', true);
}

// Without this constant, _give_die_handler() calls die() instead of returning a die
// handler, which ends the PHPUnit process with exit code 0 and leaves every remaining
// test unreported. Legacy suites define it individually, so define it once up front.
if (!defined('GIVE_UNIT_TESTS')) {
    define('GIVE_UNIT_TESTS', true);
}

// TEMPORARY: trace the caller of wp_is_block_theme() on the CI runner. Remove before merge.
TestHooks::addFilter('doing_it_wrong_run', static function ($function) {
    if ($function !== 'wp_is_block_theme') {
        return;
    }

    fwrite(STDERR, PHP_EOL . 'BLOCK_THEME_TRACE: ' . wp_debug_backtrace_summary() . PHP_EOL);
    fwrite(STDERR, 'BLOCK_THEME_CONTEXT: ' . json_encode([
        'theme_directories' => $GLOBALS['wp_theme_directories'] ?? null,
        'content_dir' => WP_CONTENT_DIR,
        'theme_root' => get_theme_root(),
        'theme_root_exists' => file_exists(get_theme_root()),
        'abspath' => ABSPATH,
        'doing' => current_filter(),
    ]) . PHP_EOL);
});

// load GiveWP
TestHooks::addFilter('muplugins_loaded', static function () {
    require_once __DIR__ . '/../give.php';
});

// install GiveWP
TestHooks::addFilter('setup_theme', static function () {
    echo 'Installing GiveWP.....' . PHP_EOL;

    // Installing runs the batch migrations, which query Action Scheduler. It does not register
    // its tables until `init`, which is later than this, so on a fresh database every one of
    // those queries fails and prints a database error.
    ActionScheduler::store()->init();

    give()->install();

    // Give_Roles::add_caps() writes capabilities to the roles array and the database but not to
    // the WP_Role objects WordPress already built, so this process would not see them until a
    // reload. On a table prefix that has never been installed, nothing else reloads them.
    wp_roles()->for_site();
});

// Eagerly declare Harbor global function stubs so they win Harbor's
// function_exists guard in vendor-prefixed/stellarwp/harbor/src/Harbor/global-functions.php.
require_once __DIR__ . '/Unit/VendorOverrides/Harbor/harbor-stub-functions.php';

// pull in WP bootstrap file which looks for WP_TESTS_CONFIG_FILE_PATH defined above
require_once $currentTestEnvironment->bootstrap();

// Include legacy test case
require_once __DIR__ . '/includes/legacy/framework/class-give-unit-test-case.php';

// Include legacy helpers
require_once __DIR__ . '/includes/legacy/framework/helpers/shims.php';
require_once __DIR__ . '/includes/legacy/framework/helpers/class-helper-form.php';
require_once __DIR__ . '/includes/legacy/framework/helpers/class-helper-payment.php';
