<?php

namespace Give\ServiceProviders;

class GlobalStyles implements ServiceProvider
{
    /** @inheritDoc */
    public function register()
    {
        // This section intentionally left blank.
    }

    /** @inheritDoc */
    public function boot()
    {
        add_action('admin_enqueue_scripts', function() {
            wp_register_style('givewp-admin-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap', [], null); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- external Google Fonts stylesheet, not versioned by GiveWP.
        }, -1);
    }
}
