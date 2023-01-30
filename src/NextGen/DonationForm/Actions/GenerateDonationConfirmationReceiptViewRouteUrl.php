<?php

namespace Give\NextGen\DonationForm\Actions;


use Give\NextGen\Framework\Routes\Route;

/**
 * @since 0.1.0
 */
class GenerateDonationConfirmationReceiptViewRouteUrl
{
    /**
     * @since 0.1.0
     */
    public function __invoke(string $receiptId): string
    {
        $args = [
            'receipt-id' => $receiptId
        ];

        return esc_url_raw(Route::url('donation-confirmation-receipt-view', $args));
    }
}
