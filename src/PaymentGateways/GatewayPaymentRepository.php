<?php

namespace Give\PaymentGateways;

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
     * @param int $donationId
     * @param string $titleLength
     *
     * @unlreased
     */
    public function getDonationTitle($donationId, $titleLength = null)
    {
        $donation = new Give_Payment($donationId);
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

        // Cut the length
        if ($titleLength) {
            return substr($donationTitle, 0, $titleLength);
        }

        return $donationTitle;
    }
}
