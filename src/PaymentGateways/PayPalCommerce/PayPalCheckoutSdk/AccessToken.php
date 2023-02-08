<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk;

/**
 * Class AccessToken.
 *
 * @unreleased x.x.x
 */
class AccessToken extends \PayPalCheckoutSdk\Core\AccessToken
{
    /**
     * Token creation date.
     *
     * @unreleased x.x.x
     * @var int
     */
    protected $nonce;

    /**
     * Returns true if the token is expired.
     *
     * @unreleased x.x.x
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
     * @unreleased x.x.x
     */
    protected function getCreationDate(): int
    {
        return strtotime(substr($this->nonce, 0, 19));
    }

    /**
     * Returns the token expiration date.
     *
     * @unreleased x.x.x
     */
    protected function getExpirationDate(): int
    {
        return $this->getCreationDate() + $this->expiresIn;
    }

    /**
     * Returns the class object.
     *
     * @unreleased x.x.x
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
     * @unreleased x.x.x
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
