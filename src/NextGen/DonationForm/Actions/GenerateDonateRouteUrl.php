<?php

namespace Give\NextGen\DonationForm\Actions;


use Give\NextGen\DonationForm\Routes\DonateRouteSignature;
use Give\NextGen\Framework\Routes\Route;

/**
 * @since 0.1.0
 */
class GenerateDonateRouteUrl
{
    /**
     * @since 0.1.0
     *
     * @return string
     *
     */
    public function __invoke(): string
    {
        $signature = new DonateRouteSignature('givewp-donate');

        $queryArgs = [
            'givewp-route-signature' => $signature->toHash(),
            'givewp-route-signature-id' => 'givewp-donate',
            'givewp-route-signature-expiration' => $signature->expiration,
        ];

        return esc_url_raw(Route::url('donate', $queryArgs));
    }
}
