<?php

namespace Give\Tests\Config;

/**
 * PHPUnit running inside the wp-env cli container, against the WordPress and test library wp-env
 * installed there. The paths are wp-env's own and are fixed: paratest workers start without the
 * container's environment, so nothing here can be read from it.
 *
 * @since 4.18.0
 */
class WpEnv implements Config
{
    /**
     * @since 4.18.0
     */
    public function config(): string
    {
        return __DIR__ . '/../wp-tests-config.wp-env.php';
    }

    /**
     * @since 4.18.0
     */
    public function bootstrap(): string
    {
        return '/wordpress-phpunit/includes/bootstrap.php';
    }
}
