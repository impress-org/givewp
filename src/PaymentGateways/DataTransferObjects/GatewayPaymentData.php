<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Give\ValueObjects\Address;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;

/**
 * Class GatewayPaymentData
 * @since 2.18.0
 */
class GatewayPaymentData
{
    /**
     * @var string
     */
    public $gatewayId;
    /**
     * @var string
     */
    public $donationId;
    /**
     * @var float
     */
    public $price;
    /**
     * @var string
     */
    public $priceId;
    /**
     * @var string
     */
    public $date;
    /**
     * @var string
     */
    public $purchaseKey;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var DonorInfo
     */
    public $donorInfo;
    /**
     * @var CardInfo|null
     */
    public $cardInfo;
    /**
     * @var string
     */
    public $amount;
    /**
     * @var Address|null
     */
    public $billingAddress;
    /**
     * @var string
     */
    public $redirectUrl;
    /**
     * We are using this property internally to gracefully deprecate filter and action hooks.
     * We do not recommend using this property in logic. This will be removed in the future.
     *
     * @deprecated
     *
     * @var array
     */
    public $legacyPaymentData;

    /**
     * Convert data from array into DTO
     *
     * @since 2.18.0
     *
     * @return self
     */
    public static function fromArray(array $array)
    {
        $self = new static();

        $self->legacyPaymentData = $array['legacyPaymentData'];
        $self->price = $array['price'];
        $self->priceId = $array['priceId'];
        $self->currency = $array['currency'];
        $self->amount = $array['amount'];
        $self->date = $array['date'];
        $self->gatewayId = $array['gatewayId'];
        $self->donationId = $array['donationId'];
        $self->purchaseKey = $array['purchaseKey'];
        $self->donorInfo = $array['donorInfo'];
        $self->cardInfo = $array['cardInfo'];
        $self->billingAddress = $array['billingAddress'];
        $self->redirectUrl = give_get_success_page_uri();

        return $self;
    }
}
