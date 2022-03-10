<?php

namespace Give\Framework\PaymentGateways\Routes;

/**
 * Route signature for creating secure gateway route methods
 *
 * @since 2.19.0
 */
class RouteSignature {
    /**
     * @var string
     */
    private $signature;

    /**
     * @since 2.19.4 - replace RouteSignature args with unique donationId
     *
     * @since 2.19.0
     *
     * @param  int  $gatewayId
     * @param  string  $gatewayMethod
     * @param  int  $donationId
     */
    public function __construct($gatewayId, $gatewayMethod, $donationId)
    {
        $this->signature = "$gatewayId@$gatewayMethod:$donationId";
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    public function toString()
    {
        return $this->signature;
    }

    /**
     * @since 2.19.0
     *
     * @return false|string
     */
    public function toNonce()
    {
        return wp_create_nonce($this->signature);
    }
}
