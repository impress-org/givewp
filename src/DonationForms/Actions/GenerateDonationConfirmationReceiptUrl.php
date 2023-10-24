<?php

namespace Give\DonationForms\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Routes\RouteListener;

class GenerateDonationConfirmationReceiptUrl
{
    /**
     * @since 3.0.0
     */
    public function __invoke(Donation $donation, string $originUrl, string $embedId = ''): string
    {
        $routeListener = new RouteListener(
            'donation-completed',
            'show-donation-confirmation-receipt'
        );

        return $routeListener->toUrl($originUrl, [
            'givewp-receipt-id' => $donation->purchaseKey,
            'givewp-embed-id' => $embedId,
        ]);
    }
}