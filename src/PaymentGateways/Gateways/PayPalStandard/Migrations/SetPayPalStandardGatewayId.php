<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class SetPayPalStandardGatewayId
 * @package Give\PaymentGateways\Gateways\PayPalStandard\Migrations
 *
 * This migration fixes a bug that was introduced in 2.9.0 wherein the PayPal Standard gateway ID was changed from
 * paypal to paypal-standard. This caused problems on existing sites using PayPal Standard. The purpose of this
 * migration is to help those that are on 2.9.0 to recover to using the paypal ID for the gateway.
 *
 * @since 2.9.1
 */
class SetPayPalStandardGatewayId extends Migration
{

    /**
     * @inheritdoc
     *
     * @since 3.4.0 confirm $gateways is an array
     */
    public function run()
    {
        // Reset paypal gateway id to paypal.
        $give_settings = give_get_settings();
        $gateways = $give_settings['gateways'];
        $updateSettings = false;

        if (is_array($gateways) && array_key_exists('paypal-standard', $gateways)) {
            unset($gateways['paypal-standard']);
            $gateways['paypal'] = '1';
            $give_settings['gateways'] = $gateways;

            $updateSettings = true;
        }

        // Reset paypal gateway custom label.
        if (isset($give_settings['gateways_label'])) {
            $gateways_label = $give_settings['gateways_label'];
            if (array_key_exists('paypal-standard', $gateways_label)) {
                $gateways_label['paypal'] = $gateways_label['paypal-standard'];
                unset($gateways_label['paypal-standard']);
                $give_settings['gateways_label'] = $gateways_label;
                $updateSettings = true;
            }
        }

        // Set paypal standard as default payment gateway.
        if ('paypal-standard' === $give_settings['default_gateway']) {
            $give_settings['default_gateway'] = 'paypal';
            $updateSettings = true;
        }

        if ($updateSettings) {
            update_option('give_settings', $give_settings);
        }
    }

    /**
     * @inheritdoc
     */
    public static function id()
    {
        return 'set_paypal_standard_id_to_paypal_from_paypal_standard';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2020-10-28');
    }
}
