<?php

namespace Give\Promotions;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 0.1.0
 */
class ServiceProviderV3 implements ServiceProviderInterface
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
            Hooks::addAction('admin_notices', \Give\Promotions\WelcomeBanner\Actions\DisplayWelcomeBanner::class);
            Hooks::addAction('wp_ajax_givewp_next_gen_welcome_banner_dismiss', \Give\Promotions\WelcomeBanner\Actions\DismissWelcomeBanner::class);
        }
    }
}
