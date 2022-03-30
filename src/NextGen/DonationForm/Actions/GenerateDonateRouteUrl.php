<?php

namespace Give\NextGen\DonationForm\Actions;


use Give\NextGen\DonationForm\Routes\DonateRouteSignature;

/**
 * @unreleased
 */
class GenerateDonateRouteUrl
{
    /**
     * @unreleased
     *
     * @return string
     *
     */
    public function __invoke()
    {
        $signature = new DonateRouteSignature('give-donate');

        $queryArgs = [
            'give-listener' => 'give-donate',
            'give-route-signature' => $signature->toHash(),
            'give-route-signature-id' => 'give-donate',
            'give-route-signature-expiration' => $signature->expiration,
        ];

        return add_query_arg(
            $queryArgs,
            home_url()
        );
    }
}
