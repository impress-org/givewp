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
     * @param  array  $args
     */
    public function __construct($gatewayId, $gatewayMethod, $args)
    {
        $secureArgs = md5(json_encode($args));

        $this->signature = "$gatewayId@$gatewayMethod:$secureArgs";
    }

    /**
     * @unreleased
     *
     * @param  int  $gatewayId
     * @param  string  $gatewayMethod
     * @param  array  $args
     *
     * @return static
     */
    public static function make($gatewayId, $gatewayMethod, $args)
    {
        return new static($gatewayId, $gatewayMethod, $args);
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
