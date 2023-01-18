<?php

namespace Give\NextGen\DonationForm\Actions;


use Give\NextGen\Framework\Routes\Route;

/**
 * @unreleased
 */
class GenerateDonationConfirmationReceiptViewRouteUrl
{
    /**
     * @unreleased
     */
    public function __invoke(string $receiptId): string
    {
        $args = [
            'receipt-id' => $receiptId
        ];

        return esc_url_raw(Route::url('donation-confirmation-receipt-view', $args));
    }
}
