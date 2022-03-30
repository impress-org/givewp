<?php

namespace Give\NextGen\DonationForm\Routes;

use Give\Framework\Shims\Shim;

/**

 * @unreleased
 */
class DonateRouteSignature
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
     * @unreleased
     *
     * @param  string  $name
     * @param  string  $expiration
     */
    public function __construct($name, $expiration = null)
    {
        $this->expiration = $expiration ?: $this->createExpirationTimestamp();
        $this->signature = $this->generateSignatureString($name, $this->expiration);
    }


    /**
     * @unreleased
     *
     * @param  string  $name
     * @param  string  $expiration
     * @return string
     */
    private function generateSignatureString($name, $expiration)
    {
        return "$name|$expiration";
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
    public function createExpirationTimestamp()
    {
        Shim::load( 'current_datetime' );
        return (string)current_datetime()->modify('+1 day')->getTimestamp();
    }


    /**
     * @unreleased
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
