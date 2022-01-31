<?php

namespace Give\Framework\PaymentGateways\Routes;

class RouteSignature {
    /**
     * @var string
     */
    private $signature;

    /**
     * @param int $gatewayId
     * @param string $gatewayMethod
     * @param array $args
     */
    public function __construct($gatewayId, $gatewayMethod, $args)
    {
        $secureArgs = md5(json_encode($args));

        $this->signature = "$gatewayId@$gatewayMethod:$secureArgs";
    }

    /**
     * @param int $gatewayId
     * @param string $gatewayMethod
     * @param array $args
     *
     * @return static
     */
    public static function make($gatewayId, $gatewayMethod, $args)
    {
        return new static($gatewayId, $gatewayMethod, $args);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->signature;
    }

    /**
     * @return false|string
     */
    public function toNonce()
    {
        return wp_create_nonce($this->signature);
    }
}
