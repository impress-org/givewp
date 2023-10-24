<?php

namespace Give\DonationForms\Routes;

/**
 * @since 3.0.0
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
     * @since 3.0.0
     */
    public function __construct(string $name, string $expiration = null)
    {
        $this->expiration = $expiration ?: $this->createExpirationTimestamp();
        $this->signature = $this->generateSignatureString($name, $this->expiration);
    }


    /**
     * @since 3.0.0
     */
    private function generateSignatureString(string $name, string $expiration): string
    {
        return "$name|$expiration";
    }

    /**
     * @since 3.0.0
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->signature;
    }

    /**
     * @since 3.0.0
     *
     * @return string
     */
    public function toHash(): string
    {
        return wp_hash($this->signature);
    }

    /**
     * Create expiration timestamp
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function createExpirationTimestamp(): string
    {
        return (string)current_datetime()->modify('+1 day')->getTimestamp();
    }


    /**
     * @since 3.0.0
     */
    public function isValid(string $suppliedSignature): bool
    {
        $isSignatureValid = hash_equals(
            $suppliedSignature,
            $this->toHash()
        );

        $isNotExpired = ((int)$this->expiration) >= current_datetime()->getTimestamp();

        return $isSignatureValid && $isNotExpired;
    }
}
