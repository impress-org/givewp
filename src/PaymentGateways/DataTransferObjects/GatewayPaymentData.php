<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Give\ValueObjects\Address;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;

/**
 * Class GatewayPaymentData
 * @unreleased
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
    public $paymentId;
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
     * Convert data from array into DTO
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromArray(array $array)
    {
        $self = new static();

        $self->price = $array['price'];
        $self->priceId = $array['priceId'];
        $self->currency = $array['currency'];
        $self->amount = $array['amount'];
        $self->date = $array['date'];
        $self->gatewayId = $array['gatewayId'];
        $self->paymentId = $array['paymentId'];
        $self->purchaseKey = $array['purchaseKey'];
        $self->donorInfo = $array['donorInfo'];
        $self->cardInfo = $array['cardInfo'];
        $self->billingAddress = $array['billingAddress'];
        $self->redirectUrl = give_get_success_page_uri();

        return $self;
    }
}
