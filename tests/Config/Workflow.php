<?php

namespace GiveTests\Config;

 /**
 * Runs the test suite for GitHub actions, which tests multiple
 * WordPress versions by leveraging wordpress-tests-lib.
 */
class Workflow implements Config
{
    /**
     * @inheritDoc
     * @unreleased
     */
    public function config(): string
    {
        return '/tmp/wordpress-tests-lib/wp-tests-config.php';
    }

    /**
     * @inheritDoc
     * @unreleased
     */
    public function bootstrap(): string
    {
        return '/tmp/wordpress-tests-lib/includes/bootstrap.php';
    }
}
