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
     * @since 2.19.4 - remove args from RouteSignature
     *
     * @since 2.19.0
     *
     * @param  int  $gatewayId
     * @param  string  $gatewayMethod
     */
    public function __construct($gatewayId, $gatewayMethod)
    {
        $this->signature = "$gatewayId@$gatewayMethod";
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
