<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;

interface OffsiteGatewayInterface
{
    /**
     * @var string[]
     */
    const defaultRouteMethods = [
        'returnFromOffsiteRedirect'
    ];

    /**
     * Return from offsite redirect
     *
     * @unreleased
     *
     * @return RedirectResponse|JsonResponse
     * @throws PaymentGatewayException|Exception
     */
    public function returnFromOffsiteRedirect($donationId);
}
