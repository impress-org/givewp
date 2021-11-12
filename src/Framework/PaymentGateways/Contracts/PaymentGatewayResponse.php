<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;

/**
 * @unreleased 
 */
interface PaymentGatewayResponse {
    /**
     * @unreleased
     *
     * @return RedirectResponse|JsonResponse
     */
    public function complete();
}