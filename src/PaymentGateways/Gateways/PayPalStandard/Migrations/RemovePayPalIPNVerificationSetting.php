<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * @since 2.15.0
 */
class RemovePayPalIPNVerificationSetting extends Migration
{

    /**
     * @inheritDoc
     */
    public function run()
    {
        // Reset paypal gateway id to paypal.
        $give_settings = give_get_settings();

        if (array_key_exists('paypal_verification', $give_settings)) {
            give_delete_option('paypal_verification');
        }
    }

    /**
     * @since 2.15.0
     * @return string
     */
    public static function id()
    {
        return 'remove-paypal-ipn-verification-setting';
    }

    /**
     * @since 2.15.0
     * @return int
     */
    public static function timestamp()
    {
        return strtotime('2021-09-28');
    }

    /**
     * @since 2.15.0
     * @return string
     */
    public static function title()
    {
        return 'Remove PayPal IPN Verification Setting';
    }
}
