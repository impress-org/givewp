<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Actions;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * This class create PayPal Standard payment gateway one time payment url on basis of donor donation query.
 *
 * @unlreased
 */
class CreatePayPalStandardPaymentURL
{
    public function __invoke(
        GatewayPaymentData $paymentData,
        $successRedirectUrl, // PayPal Standard will redirect donor to this url after successful payment.
        $failedRedirectUrl, // PayPal Standard will redirect donor to this url after failed payment.
        $payPalIpnListenerUrl // PayPal Standard will send ipn notification to this url on payment update.
    )
    {
        $paypalPaymentRedirectUrl = trailingslashit(give_get_paypal_redirect()) . '?';
        $itemName = give_payment_gateway_item_title($paymentData->legacyPaymentData);
        $payPalPartnerCode = 'givewp_SP';

        // Setup PayPal API params.
        $paypalPaymentArguments = [
            // PayPal account information
            'business' => give_get_option('paypal_email', false),

            // Donor info
            'first_name' => $paymentData->donorInfo->firstName,
            'last_name' => $paymentData->donorInfo->lastName,
            'email' => $paymentData->donorInfo->email,
            'address1' => $paymentData->billingAddress->line1,
            'address2' => $paymentData->billingAddress->line2,
            'city' => $paymentData->billingAddress->city,
            'state' => $paymentData->billingAddress->state,
            'zip' => $paymentData->billingAddress->postalCode,
            'country' => $paymentData->billingAddress->country,

            // Donation information.
            'invoice' => $paymentData->purchaseKey,
            'amount' => $paymentData->price,
            'item_name' => stripslashes($itemName),
            'currency_code' => give_get_currency($paymentData->donationId),

            // Urls
            'return' => $successRedirectUrl,
            'cancel_return' => $failedRedirectUrl,
            'notify_url' => $payPalIpnListenerUrl,

            'no_shipping' => '1',
            'shipping' => '0',
            'no_note' => '1',
            'charset' => get_bloginfo('charset'),
            'custom' => $paymentData->donationId,
            'rm' => '2',
            'page_style' => give_get_paypal_page_style(),
            'cbt' => get_bloginfo('name'),
            'bn' => $payPalPartnerCode,
        ];

        // Donations or regular transactions?
        $paypalPaymentArguments['cmd'] = give_get_paypal_button_type();

        $paypalPaymentArguments = $this->supportLegacyFilter($paypalPaymentArguments, $paymentData);

        /**
         * Filter the PayPal Standard redirect args.
         *
         * @since 2.19.0
         *
         * @param array $paypalPaymentArguments PayPal Standard payment Data.
         * @param GatewayPaymentData $paymentData Gateway payment data.
         */
        $paypalPaymentArguments = apply_filters(
            'give_gateway_paypal_redirect_args',
            $paypalPaymentArguments,
            $paymentData
        );

        $paypalPaymentRedirectUrl .= http_build_query($paypalPaymentArguments);

        // Fix for some sites that encode the entities.
        return str_replace('&amp;', '&', $paypalPaymentRedirectUrl);
    }

    /**
     * @since 2.19.0
     *
     * @param array $paypalPaymentArguments
     * @param GatewayPaymentData $paymentData
     *
     * @return array
     */
    private function supportLegacyFilter($paypalPaymentArguments, GatewayPaymentData $paymentData)
    {
        /**
         * Filter the PayPal Standard redirect args.
         *
         * @since 1.8
         */
        return apply_filters_deprecated(
            'give_paypal_redirect_args',
            [
                $paypalPaymentArguments,
                $paymentData->donationId,
                $paymentData->legacyPaymentData
            ],
            '2.19.0'
        );
    }
}
