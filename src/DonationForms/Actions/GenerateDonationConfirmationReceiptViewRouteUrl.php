<?php

namespace Give\DonationForms\Actions;


use Give\Framework\Routes\Route;

/**
 * @since 3.0.0
 */
class GenerateDonationConfirmationReceiptViewRouteUrl
{
    /**
     * @since 3.0.0
     */
    public function __invoke(string $receiptId): string
    {
        $args = [
            'receipt-id' => $receiptId
        ];

        return esc_url_raw(Route::url('donation-confirmation-receipt-view', $args));
    }
}
