<?php

return [
     /**
     * Runs the test suite for GitHub actions, which tests multiple
     * WordPress versions by leveraging wordpress-tests-lib.
     */
    'workflow' => [
        'config' => '/tmp/wordpress-tests-lib/wp-tests-config.php',
        'functions' => '/tmp/wordpress-tests-lib/includes/functions.php',
        'bootstrap' => '/tmp/wordpress-tests-lib/includes/bootstrap.php'
    ],
    /**
     * Runs the test suite for local development, which
     * is configurable for the development environment.
     */
    'local' => [
        'config' => __DIR__ . '/wp-tests-config.php',
        'functions' => '/../vendor/wordpress/wordpress/tests/phpunit/includes/functions.php',
        'bootstrap' => '/../vendor/wordpress/wordpress/tests/phpunit/includes/bootstrap.php'
    ],
];
