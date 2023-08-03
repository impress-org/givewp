<?php

namespace Give\Framework\FieldsAPI\ValueObjects;

/**
 * The scope of the field for use in persistence. The two built-in scopes are donation and donor, but the scope may be
 * any custom string. Using a custom scope allows for an add-on to either not store the field, or store it in a custom
 * location.
 *
 * @unreleased
 */
class PersistenceScope
{
    const DONATION = 'donation';
    const DONOR = 'donor';

    /**
     * @var string
     */
    private $scope;

    /**
     * @unreleased
     */
    public static function donation(): self
    {
        return new self(self::DONATION);
    }

    /**
     * @unreleased
     */
    public static function donor(): self
    {
        return new self(self::DONOR);
    }

    /**
     * @unreleased
     */
    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @unreleased
     */
    public function isDonation(): bool
    {
        return $this->scope === self::DONATION;
    }

    /**
     * @unreleased
     */
    public function isDonor(): bool
    {
        return $this->scope === self::DONOR;
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    public function __toString()
    {
        return $this->scope;
    }
}
