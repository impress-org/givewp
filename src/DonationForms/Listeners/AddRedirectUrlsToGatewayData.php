<?php

namespace Give\DonationForms\Listeners;

use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\Donations\Models\Donation;

class AddRedirectUrlsToGatewayData
{
    /**
     * This adds additional redirect urls to $gatewayData.
     *
     * This is necessary so gateways can use this value in both legacy and next gen donation forms.
     *
     * @since 3.0.0
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
