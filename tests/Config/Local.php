<?php

namespace Give\Tests\Config;

/**
 * Runs the test suite for local development, which
 * is configurable for the development environment.
 */
class Local implements Config
{
    /**
     * @inheritDoc
     * @since 2.22.1
     */
    public function config(): string
    {
        return __DIR__ . '/../wp-tests-config.php';
    }

    /**
     * @inheritDoc
     * @since 2.22.1
     */
    public function bootstrap(): string
    {
        return __DIR__ . '/../../vendor/wordpress/wordpress/tests/phpunit/includes/bootstrap.php';
    }
}
