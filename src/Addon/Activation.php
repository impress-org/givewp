<?php

namespace Give\Addon;

use Give\NextGen\Gateways\NextGenTestGateway\NextGenTestGateway;

/**
 * Example of a helper class responsible for registering and handling add-on activation hooks.
 *
 * @package     Give\Addon
 * @copyright   Copyright (c) 2020, GiveWP
 */
class Activation
{
    /**
     * Activate add-on action hook.
     *
     * @unreleased enable the NextGenTestGateway gateway by default
     * @since 0.1.0
     * @return void
     */
    public static function activateAddon()
    {
        $gateways = give_get_option('gateways');

        if (!array_key_exists(NextGenTestGateway::id(), $gateways)) {
            $gateways[NextGenTestGateway::id()] = "1";

            give_update_option('gateways', $gateways);
        }
    }

    /**
     * Deactivate add-on action hook.
     *
     * @since 0.1.0
     * @return void
     */
    public static function deactivateAddon()
    {
    }

    /**
     * Uninstall add-on action hook.
     *
     * @since 0.1.0
     * @return void
     */
    public static function uninstallAddon()
    {
    }
}
