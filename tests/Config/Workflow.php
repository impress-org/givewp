<?php

namespace Give\Tests\Config;

 /**
 * Runs the test suite for GitHub actions, which tests multiple
 * WordPress versions by leveraging wordpress-tests-lib.
 */
class Workflow implements Config
{
    /**
     * @inheritDoc
     * @since 2.20.1
     */
    public function config(): string
    {
        return '/tmp/wordpress-tests-lib/wp-tests-config.php';
    }

    /**
     * @inheritDoc
     * @since 2.20.1
     */
    public function bootstrap(): string
    {
        return '/tmp/wordpress-tests-lib/includes/bootstrap.php';
    }
}
