<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;

interface OffsiteGatewayInterface {
     /**
     * Handle returning from offsite redirect
     *
     * @unreleased
     *
     * @return void
     */
    public function handleReturnFromOffsiteRedirect();

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