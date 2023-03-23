<?php

namespace Give\NextGen\WelcomeBanner;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 0.1.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        if(!get_option('givewp_next_gen_welcome_banner_dismissed')) {
            Hooks::addAction('admin_notices', Actions\DisplayWelcomeBanner::class);
            Hooks::addAction('wp_ajax_givewp_next_gen_welcome_banner_dismiss', Actions\DismissWelcomeBanner::class);
        }
    }
}
