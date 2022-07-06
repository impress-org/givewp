<?php

namespace Give\Addon;

use Give_Addon_Activation_Banner;

/**
 * Helper class responsible for showing add-on Activation Banner.
 *
 * @package     Give\Addon\Helpers
 * @copyright   Copyright (c) 2020, GiveWP
 */
class ActivationBanner
{

    /**
     * Show activation banner
     *
     * @since 1.0.0
     * @return void
     */
    public function show()
    {
        // Check for Activation banner class.
        if ( ! class_exists('Give_Addon_Activation_Banner')) {
            include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
        }

        // Only runs on admin.
        $args = [
            'file' => GIVE_NEXT_GEN_FILE,
            'name' => GIVE_NEXT_GEN_NAME,
            'version' => GIVE_NEXT_GEN_VERSION,
            'settings_url' => '',
            'documentation_url' => 'https://givewp.com/documentation/add-ons/boilerplate/',
            'support_url' => 'https://givewp.com/support/',
            'testing' => false, // Never leave true.
        ];

        new Give_Addon_Activation_Banner($args);
    }
}
