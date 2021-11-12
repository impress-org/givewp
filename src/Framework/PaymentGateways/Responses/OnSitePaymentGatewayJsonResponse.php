<?php

namespace Give\Framework\PaymentGateways\Responses;

use Give\Framework\Http\Response\Response;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayResponse;

use function Give\Framework\Http\Response\response;

/**
 * @unreleased
 */
class OnSitePaymentGatewayJsonResponse implements PaymentGatewayResponse {
    /**
     * @var Response
     */
    public $response;

    /**
     * @unreleased
     *
     * @param  JsonResponse  $response
     */
    public function __construct(JsonResponse $response) {
        $this->response = $response;
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

        return response()->json($this->response->getData());
    }
}