<?php

namespace Give\TestData\Addons\Funds;

use Give\ServiceProviders\ServiceProvider as GiveServiceProvider;
use WP_CLI;

/**
 * Class ServiceProvider
 * @package Give\TestData\Funds
 */
class ServiceProvider implements GiveServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        // Add CLI commands
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('give test-funds', give()->make(FundCommand::class));
        }

        /**
         * Inject Fund ID into revenue data
         */
        add_filter(
            'give-test-data-revenue-definition',
            function ($args) {
                $args['fund_id'] = give(FundFactory::class)->getRandomFund();

                return $args;
            }
        );
    }
}
