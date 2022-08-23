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
    public static function make(string $gatewayTransactionId = null): PaymentCommand
    {
        return new static($gatewayTransactionId);
    }

    /**
     * @since 2.18.0
     *
     * @param  string|null  $gatewayTransactionId
     */
    public function __construct(string $gatewayTransactionId = null)
    {
        $this->gatewayTransactionId = $gatewayTransactionId;
    }

    /**
     * @since 2.22.0 add type, so it is typesafe
     *
     * @return $this
     */
    public function setPaymentNotes(string ...$paymentNotes): PaymentCommand
    {
        $this->paymentNotes = $paymentNotes;

        return $this;
    }

    /**
     * @param  string  $gatewayTransactionId
     * @return $this
     */
    public function setTransactionId(string $gatewayTransactionId): PaymentCommand
    {
        $this->gatewayTransactionId = $gatewayTransactionId;

        return $this;
    }
}
