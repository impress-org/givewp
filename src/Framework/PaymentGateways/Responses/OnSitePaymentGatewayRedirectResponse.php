<?php

namespace Give\Framework\PaymentGateways\Responses;

use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayResponse;

/**
 * @unreleased
 */
class OnSitePaymentGatewayRedirectResponse implements PaymentGatewayResponse {
    /**
     * @var int
     */
    public $paymentId;
    /**
     * @var string
     */
    public $transactionId;
    /**
     * @var RedirectResponse
     */
    public $response;

    /**
     * @unreleased
     *
     * @param RedirectResponse  $response
     * @param int  $paymentId
     * @param string $transactionId
     */
    public function __construct(RedirectResponse $response, $paymentId, $transactionId) {
        $this->response = $response;
        $this->paymentId = $paymentId;
        $this->transactionId = $transactionId;
    }

    /**
     * @unreleased 
     *
     * @inheritDoc
     */
    public function complete()
    {
         give_update_payment_status($this->paymentId);
         give_set_payment_transaction_id($this->paymentId, $this->transactionId);

         return $this->response;
    }
}