<?php

namespace Give\Addon;

use Give\Addon\Actions\AutoActivateLicense;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

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
     * @since 0.3.2 auto-activate free license key
     * @since 0.3.0 enable the TestGateway gateway by default
     * @since 0.1.0
     * @return void
     */
    public static function activateAddon()
    {
        if (!Environment::isGiveActive()) {
            return;
        }

        $gateways = give_get_option('gateways');

        if (!array_key_exists(TestGateway::id(), $gateways)) {
            $gateways[TestGateway::id()] = "1";

            give_update_option('gateways', $gateways);
        }

        give(AutoActivateLicense::class)->__invoke(
            '1591640',
            '3ecfdb07a933ada8ca7d201d6ea333b3'
        );
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
