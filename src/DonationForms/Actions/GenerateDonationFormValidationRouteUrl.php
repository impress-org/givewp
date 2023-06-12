<?php

namespace Give\DonationForms\Actions;


use Give\DonationForms\Routes\DonateRouteSignature;
use Give\Framework\Routes\Route;

/**
 * @unreleased
 */
class GenerateDonationFormValidationRouteUrl
{
    /**
     * @unreleased
     *
     * @return string
     *
     */
    public function __invoke(): string
    {
        $signature = new DonateRouteSignature('givewp-donation-form-validation');

        $queryArgs = (new GenerateDonateRouteSignatureArgs())($signature, 'givewp-donation-form-validation');

        return esc_url_raw(Route::url('validate', $queryArgs));
    }
}
