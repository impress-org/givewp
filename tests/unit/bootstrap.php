<?php

const WP_CONTENT_DIR = __DIR__;
const WP_TESTS_CONFIG_FILE_PATH = __DIR__ . '/wp-tests-config.php';

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

