<?php

namespace Give\Framework\FieldsAPI\ValueObjects;

/**
 * The scope of the field for use in persistence. The two built-in scopes are donation and donor, but the scope may be
 * any custom string. Using a custom scope allows for an add-on to either not store the field, or store it in a custom
 * location.
 *
 * @since 2.32.0
 */
class PersistenceScope
{
    public const DONATION = 'donation';
    public const SUBSCRIPTION = 'subscription';
    public const DONOR = 'donor';
    public const CALLBACK = 'callback';

    /**
     * @var string
     */
    private $scope;

    /**
     * @since 2.32.0
     */
    public static function donation(): self
    {
        return new self(self::DONATION);
    }

    /**
     * @since 2.32.0
     */
    public static function donor(): self
    {
        return new self(self::DONOR);
    }

    /**
     * @since 3.0.0
     */
    public static function subscription(): self
    {
        return new self(self::SUBSCRIPTION);
    }

    /**
     * @since 2.32.0
     */
    public static function callback(): self
    {
        return new self(self::CALLBACK);
    }

    /**
     * @since 2.32.0
     */
    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @since 2.32.0
     */
    public function isDonation(): bool
    {
        return $this->scope === self::DONATION;
    }

    /**
     * @since 3.0.0
     */
    public function isSubscription(): bool
    {
        return $this->scope === self::SUBSCRIPTION;
    }

    /**
     * @since 2.32.0
     */
    public function isDonor(): bool
    {
        return $this->scope === self::DONOR;
    }

    /**
     * @since 2.32.0
     */
    public function isCallback(): bool
    {
        return $this->scope === self::CALLBACK;
    }

    /**
     * @since 2.32.0
     *
     * @param self|string $scope
     */
    public function is($scope): bool
    {
        if ($scope instanceof self) {
            $scope = $scope->scope;
        }

        return $this->scope === $scope;
    }

    /**
     * @since 2.32.0
     */
    public function __toString()
    {
        return $this->scope;
    }
}
