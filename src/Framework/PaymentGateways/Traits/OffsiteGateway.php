<?php

namespace Give\Framework\PaymentGateways\Traits;

trait OffsiteGateway
{
    /**
     * Return from offsite redirect
     *
     * @unreleased
     *
     * @inheritDoc
     */
    abstract public function returnFromOffsiteRedirect($donationId);

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
