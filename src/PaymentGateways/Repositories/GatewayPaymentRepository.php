<?php

namespace Give\PaymentGateways\Repositories;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give_Payment;

/**
 * This repository return gateway payment data for specific donation.
 *
 * @unlreased
 */
class GatewayPaymentRepository
{
    /**
     * Get donation title.
     * This can be use as product item name form gateway payment.
     *
     * @param GatewayPaymentData $paymentData
     * @param string $titleLength
     *
     * @return false|int|string
     * @unlreased
     */
    public function getDonationTitle(GatewayPaymentData $paymentData, $titleLength = null)
    {
        $donation = new Give_Payment($paymentData->donationId);
        $formId = $donation->form_id;
        $price_id = $donation->price_id;

        $donationTitle = $donation->form_title;

        // Verify has variable prices.
        if (give_has_variable_prices($formId)) {
            $item_price_level_text = give_get_price_option_name($formId, $price_id, 0, false);

            /**
             * Output donation level text if:
             *
             * 1. It's not a custom amount
             * 2. The level field has actual text and isn't the amount (which is already displayed on the receipt).
             */
            if ('custom' !== $price_id && ! empty($item_price_level_text)) {
                // Matches a donation level - append level text.
                $donationTitle .= " - $item_price_level_text";
            }
        }

        $donationTitle = $this->supportLegacyFilter($donationTitle, $formId, $paymentData->legacyPaymentData);

        /**
         * Filter the donation title of Payment Gateway.
         *
         * @unreleased
         *
         * @param string $donationTitle Donation title.
         * @param GatewayPaymentData $paymentData Gateway payment data.
         */
        $donationTitle = apply_filters(
            'give_gateway_donation_title',
            $donationTitle,
            $paymentData
        );


        // Cut the length
        if ($titleLength) {
            return substr($donationTitle, 0, $titleLength);
        }

        return $donationTitle;
    }

    /**
     * @unreleased
     *
     * @param string $donationTitle
     * @param int $formId
     * @param array $paymentData
     *
     * @return string
     */
    private function supportLegacyFilter($donationTitle, $formId, $paymentData)
    {
        /**
         * Filter the Item Title of Payment Gateway.
         *
         * @since 1.8.14
         */
        return apply_filters_deprecated(
            'give_payment_gateway_item_title',
            [
                $donationTitle,
                $formId,
                $paymentData
            ],
            '' // TODO: add plugin version
        );
    }
}
