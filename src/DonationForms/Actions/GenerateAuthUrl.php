<?php

namespace Give\DonationForms\Actions;


use Give\DonationForms\Routes\DonateRouteSignature;
use Give\Framework\Routes\Route;

/**
 * @since 3.0.0
 */
class GenerateAuthUrl
{
    /**
     * @since 3.0.0
     */
    public function __invoke(): string
    {
        $signature = new DonateRouteSignature('givewp-donation-form-authentication');

        $queryArgs = (new GenerateDonateRouteSignatureArgs())($signature, 'givewp-donation-form-authentication');

        return esc_url_raw(Route::url('authenticate', $queryArgs));
    }
}
