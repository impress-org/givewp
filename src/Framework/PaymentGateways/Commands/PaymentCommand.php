<?php

namespace Give\Framework\PaymentGateways\Commands;

/***
 * @since 2.18.0
 */
abstract class PaymentCommand implements GatewayCommand
{
    /**
     * The Gateway Transaction / Charge Record ID
     *
     * @var string|null
     */
    public $gatewayTransactionId;

    /**
     * Notes to be added to the payment.
     *
     * @var array|string[]
     */
    public $paymentNotes = [];

    /**
     * @param  string|null  $gatewayTransactionId
     * @return static
     */
    public static function make($gatewayTransactionId = null)
    {
        return new static($gatewayTransactionId);
    }

    /**
     * @since 2.18.0
     *
     * @param  string|null  $gatewayTransactionId
     */
    public function __construct($gatewayTransactionId = null)
    {
        $this->gatewayTransactionId = $gatewayTransactionId;
    }

    /**
     * @param  string|string[]  ...$paymentNotes
     * @return $this
     */
    public function setPaymentNotes(...$paymentNotes)
    {
        $this->paymentNotes = $paymentNotes;
        return $this;
    }

    /**
     * @param  string  $gatewayTransactionId
     * @return $this
     */
    public function setTransactionId($gatewayTransactionId)
    {
        $this->gatewayTransactionId = $gatewayTransactionId;
        return $this;
    }
}
