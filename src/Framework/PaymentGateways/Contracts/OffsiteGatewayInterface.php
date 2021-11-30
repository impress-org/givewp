<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
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
     * @return GatewayCommand
     * @throws PaymentGatewayException|Exception
     */
    public function returnFromOffsiteRedirect();
}