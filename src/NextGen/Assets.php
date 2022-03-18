<?php

namespace Give\NextGen;

/**
 * Helper class responsible for loading add-on assets.
 *
 * @package     Give\Addon
 * @copyright   Copyright (c) 2020, GiveWP
 */
class Assets
{

    /**
     * Load add-on backend assets.
     *
     * @since 1.0.0
     * @return void
     */
    public static function loadBackendAssets()
    {
        wp_enqueue_style(
            'give-next-gen-style-backend',
            GIVE_NEXT_GEN_URL . 'public/css/give-next-gen-admin.css',
            [],
            GIVE_NEXT_GEN_VERSION
        );

        wp_enqueue_script(
            'give-next-gen-script-backend',
            GIVE_NEXT_GEN_URL . 'public/js/give-next-gen-admin.js',
            [],
            GIVE_NEXT_GEN_VERSION,
            true
        );
    }

    /**
     * Load add-on front-end assets.
     *
     * @since 1.0.0
     * @return void
     */
    public static function loadFrontendAssets()
    {
        wp_enqueue_style(
            'give-next-gen-style-frontend',
            GIVE_NEXT_GEN_URL . 'public/css/give-next-gen.css',
            [],
            GIVE_NEXT_GEN_VERSION
        );

        wp_enqueue_script(
            'give-next-gen-script-frontend',
            GIVE_NEXT_GEN_URL . 'public/js/give-next-gen.js',
            [],
            GIVE_NEXT_GEN_VERSION,
            true
        );
    }
}
