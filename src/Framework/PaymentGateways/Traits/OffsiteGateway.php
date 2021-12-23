<?php

namespace Give\Framework\PaymentGateways\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
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
    public function returnFromOffsiteRedirect() {
        return new PaymentProcessing();
    }

    /**
     * Generate return url from redirect offsite
     *
     * @unreleased
     *
     * @param  int  $donationId
     * @param  array|null  $args  - associative array of query args
     * @return string
     */
    public function generateReturnUrlFromRedirectOffsite($donationId, $args = null)
    {
        return $this->generateGatewayRouteUrl('returnFromOffsiteRedirect', $donationId, $args);
    }
}
