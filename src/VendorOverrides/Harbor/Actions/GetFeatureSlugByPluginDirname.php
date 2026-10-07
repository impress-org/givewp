<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Harbor\Actions;

/**
 * Harbor identifies an add-on by its catalog slug. GiveWP identifies an add-on
 * by its plugin directory name, and for the add-ons listed here the two differ.
 *
 * @since TBD
 */
class GetFeatureSlugByPluginDirname
{
    /**
     * Plugin directory name => Harbor catalog slug, for add-ons where they differ.
     *
     * @since TBD
     */
    private const SLUGS = [
        'give-2checkout' => 'give-2checkout-gateway',
        'give-authorize-net' => 'give-authorize-net-gateway',
        'give-bitpay' => 'give-bitpay-donations',
        'give-blink' => 'give-blink-gateway',
        'give-braintree' => 'give-braintree-payment-gateway',
        'give-ccavenue' => 'give-ccavenue-gateway',
        'give-donation-upsells-woocommerce' => 'give-donation-upsells-for-woocommerce',
        'give-funds' => 'give-funds-and-designations',
        'give-gocardless' => 'give-gocardless-gateway',
        'give-iats' => 'give-iats-gateway',
        'give-mollie' => 'give-mollie-payment-gateway',
        'give-moneris' => 'give-moneris-gateway',
        'give-payfast' => 'give-payfast-gateway',
        'give-paymill' => 'give-paymill-gateway',
        'give-paytm' => 'give-paytm-gateway',
        'give-payumoney' => 'give-payumoney-gateway',
        'give-razorpay' => 'give-razorpay-gateway',
        'give-recurring' => 'give-recurring-donations',
        'give-square' => 'give-square-gateway',
        'give-stripe' => 'give-stripe-gateway',
    ];

    /**
     * @since TBD
     */
    public function __invoke(string $pluginDirname): string
    {
        return self::SLUGS[$pluginDirname] ?? $pluginDirname;
    }
}
