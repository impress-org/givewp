<?php

namespace Give\Framework\PaymentGateways\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Actions\GenerateReturnUrlFromRedirectOffsite;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;

trait OffsiteGateway
{
    /**
     * Return from offsite redirect
     *
     * @unreleased
     *
     * @return GatewayCommand
     * @throws PaymentGatewayException|Exception
     */
    abstract public function returnFromOffsiteRedirect();

    /**
     * Generate return url from redirect offsite
     *
     * @param  array|null  $args  - associative array of query args
     * @return string
     */
    public function generateReturnUrlFromRedirectOffsite($paymentId, $args = null)
    {
        /** @var GenerateReturnUrlFromRedirectOffsite $action */
        $action = give(GenerateReturnUrlFromRedirectOffsite::class);

        return $action($this->getId(), 'returnFromOffsiteRedirect', $paymentId, $args);
    }
}