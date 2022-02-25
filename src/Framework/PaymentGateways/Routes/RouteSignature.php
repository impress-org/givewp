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
     * @since 2.19.0
     *
     * @param  int  $gatewayId
     * @param  string  $gatewayMethod
     * @param  string[]  $args
     */
    public function __construct($gatewayId, $gatewayMethod, $args)
    {
        $secureArgs = md5(implode('|', $args));

        $this->signature = "$gatewayId@$gatewayMethod:$secureArgs";
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
