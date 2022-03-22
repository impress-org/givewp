<?php

namespace Give\Addon;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider;

/**
 * Example of a service provider responsible for add-on initialization.
 *
 * @package     Give\Addon
 * @copyright   Copyright (c) 2020, GiveWP
 */
class AddonServiceProvider implements ServiceProvider
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
        Hooks::addFilter('plugin_action_links_' . GIVE_NEXT_GEN_BASENAME, Links::class);

        if (is_admin()) {
            $this->loadBackend();
        }
    }

    /**
     * Load add-on backend assets.
     *
     * @return void
     * @since 1.0.0
     */
    private function loadBackend()
    {
        Hooks::addAction('admin_init', License::class, 'check');
        Hooks::addAction('admin_init', ActivationBanner::class, 'show', 20);
    }
}
