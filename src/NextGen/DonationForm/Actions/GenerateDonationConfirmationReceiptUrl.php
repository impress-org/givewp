<?php

namespace Give\NextGen\DonationForm\Actions;

use Give\Donations\Models\Donation;
use Give\NextGen\Framework\Routes\RouteListener;

class GenerateDonationConfirmationReceiptUrl
{
    /**
     * @since 0.1.0
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