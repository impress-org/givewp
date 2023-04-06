<?php

namespace Give\Framework\DesignSystem;

use Give\Framework\DesignSystem\Actions\RegisterDesignSystemStyles;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider;

class DesignSystemServiceProvider implements ServiceProvider
{
    /**
     * @since 2.26.0
     */
    public function register()
    {
    }

    /**
     * @since 2.26.0
     */
    public function boot()
    {
        Hooks::addAction('wp_enqueue_scripts', RegisterDesignSystemStyles::class);
        Hooks::addAction('admin_enqueue_scripts', RegisterDesignSystemStyles::class);
    }
}
