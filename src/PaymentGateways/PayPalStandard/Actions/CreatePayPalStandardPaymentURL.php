<?php

namespace Give\PaymentGateways\PayPalStandard\Actions;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\ValueObjects\Address;

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
        $itemName = $paymentData->getDonationTitle();
        $payPalPartnerCode = 'givewp_SP';

        // Setup PayPal API params.
        $paypalPaymentArguments = [
            // PayPal account information
            'business' => give_get_option('paypal_email', false),

            // Donor info
            'first_name' => $paymentData->donorInfo->firstName,
            'last_name' => $paymentData->donorInfo->lastName,
            'email' => $paymentData->donorInfo->email,

            // Donation information.
            'invoice' => $paymentData->purchaseKey,
            'amount' => $paymentData->amount,
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

        if ($paymentData->donorInfo->address instanceof Address) {
            // Donor address
            $paypalPaymentArguments['address1'] = $paymentData->donorInfo->address->line1;
            $paypalPaymentArguments['address2'] = $paymentData->donorInfo->address->line2;
            $paypalPaymentArguments['city'] = $paymentData->donorInfo->address->city;
            $paypalPaymentArguments['state'] = $paymentData->donorInfo->address->state;
            $paypalPaymentArguments['zip'] = $paymentData->donorInfo->address->postalCode;
            $paypalPaymentArguments['country'] = $paymentData->donorInfo->address->country;
        }


        // Donations or regular transactions?
        $paypalPaymentArguments['cmd'] = give_get_paypal_button_type();

        $paypalPaymentArguments = $this->supportLegacyFilter($paypalPaymentArguments, $paymentData);

        /**
         * Filter the PayPal Standard redirect args.
         *
         * @unreleased
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
     * @unreleased
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
            '' // TODO: add plugin version: @unreleased
        );
    }
}
