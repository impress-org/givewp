<?php

namespace Give\NextGen\Gateways\Stripe;

use Give\Donations\Models\Donation;
use Give\Helpers\Gateways\Stripe;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\NextGenStripeGateway;

class LegacyStripeAdapter
{
    /**
     * This adds the Stripe details to the donation details page.
     *
     * @unreleased
     */
    public function addDonationDetails()
    {
        /**
         * Transaction ID link in donation details
         */
        add_filter(
            sprintf('give_payment_details_transaction_id-%s', NextGenStripeGateway::id()),
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

            if ($donation->gatewayId === NextGenStripeGateway::id()) {
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

            if ($donation->gatewayId === NextGenStripeGateway::id()) {
                Stripe::addAccountDetail($donationId, $donation->formId);
            }
        });
    }
}