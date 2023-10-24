<?php

namespace Give\DonationForms\Actions;


use Give\DonationForms\Routes\DonateRouteSignature;
use Give\Framework\Routes\Route;

/**
 * @since 3.0.0
 */
class GenerateDonateRouteUrl
{
    /**
     * @since 3.0.0
     *
     * @return string
     *
     */
    public function __invoke(): string
    {
        $signature = new DonateRouteSignature('givewp-donate');

        $queryArgs = (new GenerateDonateRouteSignatureArgs())($signature, 'givewp-donate');

        return esc_url_raw(Route::url('donate', $queryArgs));
    }
}
