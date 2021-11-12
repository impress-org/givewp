<?php

namespace Give\Framework\PaymentGateways\Responses;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayResponse;

/**
 * @unreleased
 */
class OnSitePaymentGatewayRedirectResponse extends PaymentGatewayResponse
{
    /**
     * @var int
     */
    public $paymentId;
    /**
     * @var string
     */
    public $transactionId;
    /**
     * @var string
     */
    public $redirectUrl;

    /**
     * @unreleased
     *
     * @param  string  $redirectUrl
     * @param  int  $paymentId
     * @param  string  $transactionId
     */
    public function __construct($redirectUrl, $paymentId, $transactionId)
    {
        $this->redirectUrl = $redirectUrl;
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
        $this->updatePaymentMeta($this->paymentId, $this->transactionId);

        return $this->response()->redirectTo($this->redirectUrl);
    }
}