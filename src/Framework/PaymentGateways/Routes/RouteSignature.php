<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\Shims\Shim;

/**
 * Route signature for creating secure gateway route methods
 *
 * @since 2.19.0
 */
class RouteSignature
{
    /**
     * @var string
     */
    private $signature;
    /**
     * @var string
     */
    public $expiration;

    /**
     * @since 2.19.5 replace wp_create_nonce with wp_hash and timestamp expiration
     * @since 2.19.4 replace RouteSignature args with unique donationId
     * @since 2.19.0
     *
     * @param  int  $gatewayId
     * @param  string  $gatewayMethod
     * @param  int  $donationId
     * @param  string  $expiration
     */
    public function __construct($gatewayId, $gatewayMethod, $donationId, $expiration = null)
    {
        $this->expiration = $expiration ?: $this->createExpirationTimestamp();
        $this->signature = $this->generateSignatureString($gatewayId, $gatewayMethod, $donationId, $this->expiration);
    }


    /**
     * @since 2.19.5
     *
     * @param  string  $gatewayId
     * @param  string  $gatewayMethod
     * @param  int  $donationId
     * @param  string  $expiration
     * @return string
     */
    private function generateSignatureString($gatewayId, $gatewayMethod, $donationId, $expiration)
    {
        return "$gatewayId@$gatewayMethod:$donationId|$expiration";
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
     * @since 2.19.5
     *
     * @return string
     */
    public function toHash()
    {
        return wp_hash($this->signature);
    }

    /**
     * Create expiration timestamp
     *
     * @since 2.19.5
     *
     * @return string
     */
    public function createExpirationTimestamp()
    {
        return (string)current_datetime()->modify('+1 day')->getTimestamp();
    }


    /**
     * @since 2.19.5
     *
     * @param  string  $suppliedSignature
     * @return bool
     */
    public function isValid($suppliedSignature)
    {
        $isSignatureValid = hash_equals(
            $suppliedSignature,
            $this->toHash()
        );

        $isNotExpired = ((int)$this->expiration) >= current_datetime()->getTimestamp();

        return $isSignatureValid && $isNotExpired;
    }
}
