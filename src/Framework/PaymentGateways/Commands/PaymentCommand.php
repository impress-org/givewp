<?php

namespace Give\Framework\PaymentGateways\Commands;

/***
 * @unreleased
 */
abstract class PaymentCommand implements GatewayCommand
{
    /**
     * The Gateway Transaction / Charge Record ID
     *
     * @var string|null
     */
    public $gatewayTransactionId;

    public $paymentNotes = [];

    public static function make($gatewayTransactionId = null)
    {
        return new static($gatewayTransactionId);
    }

    /**
     * @unreleased
     *
     * @param  string|null  $gatewayTransactionId
     */
    public function __construct($gatewayTransactionId = null)
    {
        $this->gatewayTransactionId = $gatewayTransactionId;
    }

    public function withNote( ...$paymentNotes )
    {
        $this->paymentNotes = $paymentNotes;
        return $this;
    }
}
