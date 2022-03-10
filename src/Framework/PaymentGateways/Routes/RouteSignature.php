<?php

namespace Give\Framework\PaymentGateways\Routes;

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
     * @since 2.19.4 replace RouteSignature args with unique donationId
     *
     * @since 2.19.0
     *
     * @param  int  $gatewayId
     * @param  string  $gatewayMethod
     * @param  int  $donationId
     * @param  string  $expiration
     */
    public function __construct($gatewayId, $gatewayMethod, $donationId, $expiration = null)
    {
        $this->expiration = $expiration ?: self::createExpirationTimestamp();
        $this->signature = $this->generateSignatureString($gatewayId, $gatewayMethod, $donationId, $this->expiration);
    }


    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     *
     * @return string
     */
    public static function createExpirationTimestamp()
    {
        return (string)current_datetime()->modify('+1 day')->getTimestamp();
    }


    /**
     * @unreleased
     *
     * @param  string  $suppliedSignature
     * @param  string  $expiration
     * @return bool
     */
    public function isValid($suppliedSignature, $expiration)
    {
        $isSignatureValid = hash_equals(
            $suppliedSignature,
            $this->toHash()
        );

        // expiration should be in the future
        $isNotExpired = ((int)$expiration) >= current_datetime()->getTimestamp();

        return $isSignatureValid && $isNotExpired;
    }
}
