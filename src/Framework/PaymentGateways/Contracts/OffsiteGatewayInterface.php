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
        'returnSuccessFromOffsiteRedirect',
        'returnFailureFromOffsiteRedirect',
        'returnCancelFromOffsiteRedirect'
    ];

    /**
     * Return from offsite redirect when payment completed successfully
     *
     * @unreleased
     *
     * @return RedirectResponse|JsonResponse
     * @throws PaymentGatewayException|Exception
     */
    public function returnSuccessFromOffsiteRedirect($donationId);

    /**
     * Return from offsite redirect when payment failed
     *
     * @unreleased
     *
     * @return RedirectResponse|JsonResponse
     * @throws PaymentGatewayException|Exception
     */
    public function returnFailureFromOffsiteRedirect($donationId);

    /**
     * Return from offsite redirect when payment cancelled/denied by donor
     *
     * @unreleased
     *
     * @return RedirectResponse|JsonResponse
     * @throws PaymentGatewayException|Exception
     */
    public function returnCancelFromOffsiteRedirect($donationId);
}
