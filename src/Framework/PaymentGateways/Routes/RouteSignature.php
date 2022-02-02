<?php

namespace Give\Framework\PaymentGateways\Routes;

/**
 * Route signature for creating secure gateway route methods
 *
 * @unreleased
 */
class RouteSignature {
    /**
     * @var string
     */
    private $signature;

    /**
     * @unreleased
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
     * @unreleased
     *
     * @return string
     */
    public function toString()
    {
        return $this->signature;
    }

    /**
     * @unreleased
     *
     * @return false|string
     */
    public function toNonce()
    {
        return wp_create_nonce($this->signature);
    }
}
