<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Donations\Models\Donation;
use Give\Helpers\Gateways\Stripe;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give_Stripe;

class LegacyStripeAdapter
{
    /**
     * Legacy Stripe gates these files by the use of give_stripe_supported_payment_methods.
     * This makes it possible to load the files without having to enable a legacy stripe gateway.
     * This also makes it possible to load the files without the use of the give_stripe_supported_payment_methods filter.
     *
     * @since 3.0.0
     */
    public function loadLegacyStripeWebhooksAndFilters()
    {
        $settings = give_get_settings();
        $gatewaysFromSettings = $settings['gateways'] ?? [];
        $gatewaysFromOption = give_get_option('gateways_v3', []);

        // for some reason, the gateways from the settings are not always in the gateways from the option.
        // this might be a service provider race conditions.
        // for now im merging the two arrays to make sure we're checking both places..
        $gateways = array_merge(
            $gatewaysFromOption,
            $gatewaysFromSettings
        );

        if (!class_exists('Give_Stripe_Webhooks') && array_key_exists(StripePaymentElementGateway::id(), $gateways)) {
            (new Give_Stripe())->include_frontend_files();
        }
    }

    /**
     * This adds the Next Gen Stripe Gateway to the list of give_stripe_supported_payment_methods.
     *
     * If this is not included, then the webhooks will not be registered unless a legacy stripe gateway is enabled.
     *
     * @since 3.0.0
     */
    public function addToStripeSupportedPaymentMethodsList()
    {
        add_filter('give_stripe_supported_payment_methods', static function ($gateways) {
            $gatewayId = StripePaymentElementGateway::id();

            if (!in_array($gatewayId, $gateways, true)) {
                $gateways[] = $gatewayId;
            }

            return $gateways;
        });
    }

    /**
     * This adds the Stripe details to the donation details page.
     *
     * @since 3.0.0
     */
    public function addDonationDetails()
    {
        /**
         * Transaction ID link in donation details
         */
        add_filter(
            sprintf('give_payment_details_transaction_id-%s', StripePaymentElementGateway::id()),
            'give_stripe_link_transaction_id',
            10,
            2
        );

        /**
         * Displays the stripe account details on donation details page.
         */
        add_action('give_view_donation_details_payment_meta_after', static function ($donationId) {
            /** @var Donation $donation */
            $donation = Donation::find($donationId);

            if ($donation->gatewayId === StripePaymentElementGateway::id()) {
                $stripeAccounts = give_stripe_get_all_accounts();
                $accountId = give_get_meta($donationId, '_give_stripe_account_slug', true);
                $accountDetail = $stripeAccounts[$accountId] ?? [];
                $account = 'connect' === $accountDetail['type'] ?
                    "{$accountDetail['account_name']} ({$accountId})" :
                    give_stripe_convert_slug_to_title($accountId);
                ?>
                <div class="give-donation-stripe-account-used give-admin-box-inside">
                    <p>
                        <strong><?php
                            esc_html_e('Stripe Account:', 'give'); ?></strong><br/>
                        <?php
                        echo $account; ?>
                    </p>
                </div>
                <?php
            }
        });

        /**
         * Adds the stripe account details to donation notes and donation meta.
         */
        add_action('give_insert_payment', static function ($donationId) {
            /** @var Donation $donation */
            $donation = Donation::find($donationId);

            if ($donation->gatewayId === StripePaymentElementGateway::id()) {
                Stripe::addAccountDetail($donationId, $donation->formId);
            }
        });
    }
}
