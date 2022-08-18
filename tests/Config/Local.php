<?php

namespace GiveTests\Config;

/**
 * Runs the test suite for local development, which
 * is configurable for the development environment.
 */
class Local implements Config {

    public function config(): string
    {
        return __DIR__ . '/../wp-tests-config.php';
    }

    public function bootstrap(): string
    {
        return __DIR__ . '/../../vendor/wordpress/wordpress/tests/phpunit/includes/bootstrap.php';
    }

    public function functions(): string
    {
        return __DIR__ . '/../../vendor/wordpress/wordpress/tests/phpunit/includes/functions.php';
    }
}
