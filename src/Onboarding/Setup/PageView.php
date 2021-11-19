<?php

/**
 * Onboarding class
 *
 * @package Give
 */

namespace Give\Onboarding\Setup;

defined('ABSPATH') || exit;

use Give\Helpers\Gateways\Stripe;
use Give\Onboarding\FormRepository;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;

/**
 * @since 2.8.0
 */
class PageView
{

    /** @var FormRepository */
    protected $formRepository;

    /**
     * @since 2.8.0
     *
     * @param FormRepository $formRepository
     *
     */
    public function __construct(FormRepository $formRepository)
    {
        $this->formRepository = $formRepository;
    }

    public function render()
    {
        $settings = wp_parse_args(
            get_option('give_onboarding', []),
            [
                'addons' => [],
            ]
        );
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/index.html.php';

        return ob_get_clean();
    }

    /**
     * Render templates
     *
     * @since 2.8.0
     *
     * @param string $template
     * @param array  $data The key/value pairs passed as $data are extracted as variables for use within the template file.
     *
     * @return string
     */
    public function render_template($template, $data = [])
    {
        ob_start();
        include plugin_dir_path(__FILE__) . "templates/$template.html";
        $output = ob_get_clean();

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode('', $value);
            }
            $output = preg_replace('/{{\s*' . $key . '\s*}}/', $value, $output);
        }

        // Stripe unmerged tags.
        $output = preg_replace('/{{\s*\w*\s*}}/', '', $output);

        return $output;
    }

    /**
     * @return bool
     */
    public function isFormConfigured()
    {
        return ! ! $this->formRepository->getDefaultFormID();
    }

    /**
     * @since 2.8.0
     * @return bool
     *
     */
    public function isStripeSetup()
    {
        return Stripe::isAccountConfigured();
    }

    /**
     * Return whether ot not PayPal account connected.
     *
     * @since 2.8.0
     * @return bool
     *
     */
    public function isPayPalSetup()
    {
        return (bool)give(MerchantDetails::class)->accountIsConnected();
    }

    /**
     * Returns a qualified image URL.
     *
     * @param string $src
     *
     * @return string
     */
    public function image($src)
    {
        return GIVE_PLUGIN_URL . "assets/dist/images/setup-page/$src";
    }

    /**
     * Prepared Stripe Connect URL
     *
     * Copied from includes/gateways/stripe/includes/admin/admin-helpers.php
     *      See `give_stripe_connect_button()`
     *
     * @since 2.8.0
     */
    public function stripeConnectURL()
    {
        return add_query_arg(
            [
                'stripe_action' => 'connect',
                'mode' => give_is_test_mode() ? 'test' : 'live',
                'return_url' => rawurlencode(
                    admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings')
                ),
                'website_url' => get_bloginfo('url'),
                'give_stripe_connected' => '0',
            ],
            esc_url_raw('https://connect.givewp.com/stripe/connect.php')
        );
    }
}
