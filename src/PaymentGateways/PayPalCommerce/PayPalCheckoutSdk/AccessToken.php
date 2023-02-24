<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk;

/**
 * Class AccessToken.
 *
 * @since 2.25.0
 */
class AccessToken extends \PayPalCheckoutSdk\Core\AccessToken
{
    /**
     * Token creation date.
     *
     * @since 2.25.0
     * @var int
     */
    protected $nonce;

    /**
     * Returns true if the token is expired.
     *
     * @since 2.25.0
     */
    public function isExpired(): bool
    {
        return time() >= $this->getExpirationDate();
    }

    /**
     * Returns the token creation date.
     *
     * The creation date is the first 19 characters of the nonce.
     * Example nonce: 2023-02-07T05:03:17ZPeYxT6_thWGlTaamtMGYt5RQzVHx5B4dlNjLNhoF0tM
     *
     * @since 2.25.0
     */
    protected function getCreationDate(): int
    {
        return strtotime(substr($this->nonce, 0, 19));
    }

    /**
     * Returns the token expiration date.
     *
     * @since 2.25.0
     */
    protected function getExpirationDate(): int
    {
        return $this->getCreationDate() + $this->expiresIn;
    }

    /**
     * Returns the class object.
     *
     * @since 2.25.0
     */
    public static function fromArray(array $data): self
    {
        $accessToken = new self(
            $data['accessToken'],
            $data['tokenType'],
            $data['expiresIn']
        );
        $accessToken->nonce = $data['nonce'];

        return $accessToken;
    }

    /**
     * Returns the class object.
     *
     * @since 2.25.0
     */
    public static function fromObject(\stdClass $data): self
    {
        $accessToken = new self(
            $data->accessToken,
            $data->tokenType,
            $data->expiresIn
        );
        $accessToken->nonce = $data->nonce;

        return $accessToken;
    }
}
