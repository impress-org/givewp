<?php
const WP_CONTENT_DIR = __DIR__;

$testConfig = [
    'workflow' => '/tmp/wordpress-tests-lib/wp-tests-config.php',
    'local' => __DIR__ . '/wp-tests-config.php',
];

if( file_exists( $testConfig[ 'workflow' ] ) ) {

    /**
     * Runs the test suite for GitHub actions, which tests multiple
     * WordPress versions by leveraging wordpress-tests-lib.
     */

    define('WP_TESTS_CONFIG_FILE_PATH', $testConfig[ 'workflow' ] );
    require_once WP_TESTS_CONFIG_FILE_PATH;
    require_once '/tmp/wordpress-tests-lib/includes/functions.php';

    tests_add_filter('muplugins_loaded', function() {
        require_once __DIR__ . '/../../give.php';
    });
    tests_add_filter('setup_theme', function() {
        give()->install();
    });
    require_once '/tmp/wordpress-tests-lib/includes/bootstrap.php';

} elseif( file_exists( $testConfig[ 'local' ] ) ) {

    /**
     * Runs the test suite for local development, which
     * is configurable for the development environment.
     */

    define('WP_TESTS_CONFIG_FILE_PATH', $testConfig[ 'local' ] );

    require_once WP_TESTS_CONFIG_FILE_PATH;
    require_once __DIR__ . '/../../vendor/wordpress/wordpress/tests/phpunit/includes/functions.php';

    tests_add_filter('muplugins_loaded', function() {
        require_once __DIR__ . '/../../give.php';
    });
    tests_add_filter('setup_theme', function() {
        echo 'Installing GiveWP.....' . PHP_EOL;
        give()->install();

        echo 'Updating current user capabilities.....' . PHP_EOL;
        // reload capabilities after install, see https://core.trac.wordpress.org/ticket/28374
        $current_user = new WP_User( 1 );
        $current_user->set_role( 'editor' );
        $current_user->set_role( 'administrator' );
        wp_update_user(
            array(
                'ID'         => 1,
                'first_name' => 'Admin',
                'last_name'  => 'User',
            )
        );
    });
    require_once __DIR__ . '/../../vendor/wordpress/wordpress/tests/phpunit/includes/bootstrap.php';

} else {
    die('wp-tests-config.php not found');
}

// test cases
require_once __DIR__ . '/framework/class-give-unit-test-case.php';

// Helpers
require_once __DIR__ . '/framework/helpers/shims.php';
require_once __DIR__ . '/framework/helpers/class-helper-form.php';
require_once __DIR__ . '/framework/helpers/class-helper-payment.php';

