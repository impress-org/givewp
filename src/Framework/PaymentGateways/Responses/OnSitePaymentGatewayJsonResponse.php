<?php

namespace Give\Framework\PaymentGateways\Responses;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayResponse;

/**
 * @unreleased
 */
class OnSitePaymentGatewayJsonResponse extends PaymentGatewayResponse
{
    /**
     * @var array
     */
    public $data;

    /**
     * @unreleased
     *
     * @param  array  $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function complete()
    {
        // update payment status
        // set payment transaction ID

        return $this->response()->json($this->data);
    }
}