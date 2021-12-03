<?php

const WP_CONTENT_DIR = __DIR__;

$testConfig = [
    'workflow' => '/tmp/wordpress-tests-lib/wp-tests-config.php',
    'local' => __DIR__ . '/wp-tests-config.dist.php',
    'default' => __DIR__ . '/wp-tests-config.php',
];

if( file_exists( $testConfig[ 'workflow' ] ) ) {
    var_dump('Test with workflow config.');
    define('WP_TESTS_CONFIG_FILE_PATH', $testConfig[ 'workflow' ] );
} elseif( file_exists( $testConfig[ 'local' ] ) ) {
    var_dump('Test with local config.');
    define('WP_TESTS_CONFIG_FILE_PATH', $testConfig[ 'local' ] );
} else {
    var_dump('Test with default config.');
    define('WP_TESTS_CONFIG_FILE_PATH', $testConfig[ 'default' ] );
}

require_once __DIR__ . '/../../vendor/wordpress/wordpress/tests/phpunit/includes/functions.php';

tests_add_filter('muplugins_loaded', function() {
    require_once __DIR__ . '/../../give.php';
});

tests_add_filter('setup_theme', function() {
    give()->install();
});

require_once __DIR__ . '/../../vendor/wordpress/wordpress/tests/phpunit/includes/bootstrap.php';

// test cases
require_once __DIR__ . '/framework/class-give-unit-test-case.php';

// Helpers
require_once __DIR__ . '/framework/helpers/shims.php';
require_once __DIR__ . '/framework/helpers/class-helper-form.php';
require_once __DIR__ . '/framework/helpers/class-helper-payment.php';

