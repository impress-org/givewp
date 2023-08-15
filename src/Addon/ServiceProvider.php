<?php

namespace Give\Addon;

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
        give()->singleton(Activation::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        // Load add-on translations.
        Hooks::addAction('init', Language::class, 'load');
        // Load add-on links.
        //Hooks::addFilter('plugin_action_links_' . GIVE_NEXT_GEN_BASENAME, Links::class);

        if (is_admin()) {
            Hooks::addAction('admin_init', License::class, 'check');
            //Hooks::addAction('admin_init', ActivationBanner::class, 'show', 20);
        }
    }
}
