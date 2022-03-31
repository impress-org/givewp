<?php

namespace Give\PaymentGateways\DataTransferObjects;

/**
 * Class GiveInsertPaymentData
 *
 * This is used to expose data for use with give_insert_payment
 *
 * @since 2.18.0
 */
class GiveInsertPaymentData
{
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
     * @var string
     */
    public $formTitle;
    /**
     * @var int
     */
    public $formId;
    /**
     * @var array
     */
    public $userInfo;
    /**
     * @var string
     */
    public $donorEmail;
    /**
     * @var string
     */
    public $paymentGateway;

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

        $self->price = $array['price'];
        $self->priceId = $array['priceId'];
        $self->formTitle = $array['formTitle'];
        $self->formId = $array['formId'];
        $self->currency = $array['currency'];
        $self->date = $array['date'];
        $self->purchaseKey = $array['purchaseKey'];
        $self->donorEmail = $array['donorEmail'];
        $self->userInfo = $array['userInfo'];
        $self->paymentGateway = $array['paymentGateway'];

        return $self;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'price' => $this->price,
            'give_form_title' => $this->formTitle,
            'give_form_id' => $this->formId,
            'give_price_id' => $this->priceId,
            'date' => $this->date,
            'user_email' => $this->donorEmail,
            'purchase_key' => $this->purchaseKey,
            'currency' => $this->currency,
            'user_info' => $this->userInfo,
            'status' => 'pending',
        ];
    }
}
