<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\DonationSummary;

/**
 * This class create PayPal Standard payment gateway one time payment url on basis of donor donation query.
 *
 * @since 2.22.2
 */
class CreatePayPalStandardPaymentURL
{
    public function __invoke(
        Donation $donation,
        $successRedirectUrl, // PayPal Standard will redirect donor to this url after successful payment.
        $failedRedirectUrl, // PayPal Standard will redirect donor to this url after failed payment.
        $payPalIpnListenerUrl // PayPal Standard will send ipn notification to this url on payment update.
    )
    {
        $paypalPaymentRedirectUrl = trailingslashit(give_get_paypal_redirect()) . '?';
        $itemName = (new DonationSummary($donation))->getSummary();
        $payPalPartnerCode = 'givewp_SP';
        // Setup PayPal API params.
        $paypalPaymentArguments = [
            // PayPal account information
            'business' => give_get_option('paypal_email', false),

            // Donor info
            'first_name' => $donation->firstName,
            'last_name' => $donation->lastName,
            'email' => $donation->email,
            'address1' => $donation->billingAddress->address1,
            'address2' => $donation->billingAddress->address2,
            'city' => $donation->billingAddress->city,
            'state' => $donation->billingAddress->state,
            'zip' => $donation->billingAddress->zip,
            'country' => $donation->billingAddress->country,

            // Donation information.
            'invoice' => $donation->purchaseKey,
            'amount' => $donation->amount->formatToDecimal(),
            'item_name' => stripslashes($itemName),
            'currency_code' => give_get_currency($donation->id),

            // Urls
            'return' => $successRedirectUrl,
            'cancel_return' => $failedRedirectUrl,
            'notify_url' => $payPalIpnListenerUrl,

            'no_shipping' => '1',
            'shipping' => '0',
            'no_note' => '1',
            'charset' => get_bloginfo('charset'),
            'custom' => $donation->id,
            'rm' => '2',
            'page_style' => give_get_paypal_page_style(),
            'cbt' => get_bloginfo('name'),
            'bn' => $payPalPartnerCode,
        ];

        // Donations or regular transactions?
        $paypalPaymentArguments['cmd'] = give_get_paypal_button_type();

        $paypalPaymentArguments = $this->supportLegacyFilter($paypalPaymentArguments, $donation);

        /**
         * Filter the PayPal Standard redirect args.
         *
         * @since 2.19.0
         *
         * @param array $paypalPaymentArguments PayPal Standard payment Data.
         * @param Donation $paymentData Gateway payment data.
         */
        $paypalPaymentArguments = apply_filters(
            'give_gateway_paypal_redirect_args',
            $paypalPaymentArguments,
            $donation
        );

        $paypalPaymentRedirectUrl .= http_build_query($paypalPaymentArguments);

        // Fix for some sites that encode the entities.
        return str_replace('&amp;', '&', $paypalPaymentRedirectUrl);
    }

    /**
     * @since 2.19.0
     */
    private function supportLegacyFilter(array $paypalPaymentArguments, Donation $donation): array
    {
        /**
         * Filter the PayPal Standard redirect args.
         *
         * @since 2.21.0 Create and pass legacy payment data to filter hook
         * @since 1.8
         */
        return apply_filters_deprecated(
            'give_paypal_redirect_args',
            [
                $paypalPaymentArguments,
                $paypalPaymentArguments['custom'],
                [
                    'price' => $donation->amount->formatToDecimal(),
                    'purchase_key' => $donation->purchaseKey,
                    'user_email' => $donation->email,
                    'date' => $donation->createdAt->format('Y-m-d H:i:s'),
                    'post_data' => give_clean($_POST),
                    'gateway' => $donation->gatewayId,
                ]
            ],
            '2.19.0'
        );
    }
}
