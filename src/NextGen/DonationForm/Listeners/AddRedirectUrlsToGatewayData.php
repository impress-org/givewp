<?php

namespace Give\NextGen\DonationForm\Listeners;

use Give\Donations\Models\Donation;
use Give\NextGen\DonationForm\DataTransferObjects\DonateControllerData;

class AddRedirectUrlsToGatewayData
{
    /**
     * This adds additional redirect urls to $gatewayData.
     *
     * This is necessary so gateways can use this value in both legacy and next gen donation forms.
     *
     * @unreleased move to an action
     * @since 0.1.0
     *
     * @return void
     */
    public function __invoke(DonateControllerData $formData, Donation $donation)
    {
        $args = [
            'successUrl' => rawurlencode($formData->getSuccessUrl($donation)),
            'cancelUrl' => rawurlencode($formData->getCancelUrl())
        ];

        add_filter(
            "givewp_create_payment_gateway_data_{$donation->gatewayId}",
            static function ($data) use ($args) {
                return array_merge($data, $args);
            }
        );

        add_filter(
            "givewp_create_subscription_gateway_data_{$donation->gatewayId}",
            static function ($data) use ($args) {
                return array_merge($data, $args);
            }
        );
    }
}