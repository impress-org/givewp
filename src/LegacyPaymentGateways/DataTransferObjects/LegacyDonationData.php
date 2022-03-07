<?php

namespace Give\LegacyPaymentGateways\DataTransferObjects;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * @since 2.19.0
 */
class LegacyDonationData
{
    /** @var GatewayPaymentData */
    protected $paymentData;

    /** @var string */
    protected $paymentMethodId;

    /**
     * @since 2.19.0
     * @param GatewayPaymentData $paymentData
     * @param $paymentMethodId
     */
    public function __construct(GatewayPaymentData $paymentData, $paymentMethodId )
    {
        $this->paymentData = $paymentData;
        $this->paymentMethodId = $paymentMethodId;
    }

    /**
     * @since 2.19.0
     * @return array
     */
    public function toArray()
    {
        return [
            'source_id' => $this->paymentMethodId,
            'donation_id' => $this->paymentData->donationId,
            'post_data' => [
                'give-form-id' => give_get_payment_form_id( $this->paymentData->donationId ),
                'give-price-id' => $this->paymentData->priceId,
                'give-form-title' => give_get_donation_form_title( $this->paymentData->donationId ),
            ],
            'user_info' => [
                'first_name' => $this->paymentData->donorInfo->firstName,
                'last_name' => $this->paymentData->donorInfo->lastName,
                'user_email' => $this->paymentData->donorInfo->email,
            ],
        ];
    }

    /**
     * @since 2.19.0
     * @return array
     */
    public function toArrayWithDescription()
    {
        return $this->toArray() + [
            'description' => give_payment_gateway_donation_summary( $this->toArray(), false )
        ];
    }
}
