<?php

namespace Give\Framework\FieldsAPI\Properties\DonationForm;

use JsonSerializable;

/**
 * @since 3.0.0
 */
class CurrencySwitcherSetting implements JsonSerializable
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var float
     */
    protected $exchangeRate;
    /**
     * @var string[]
     */
    protected $gateways = [];
    /**
     * @var int
     */
    protected $exchangeRateFractionDigits;

    /**
     * @since 3.0.0
     */
    public function __construct(
        string $id,
        float $exchangeRate = 0,
        array $gateways = [],
        int $exchangeRateFractionDigits = 2
    ) {
        $this->id = $id;
        $this->exchangeRate = $exchangeRate;
        $this->gateways = $gateways;
        $this->exchangeRateFractionDigits = $exchangeRateFractionDigits;
    }

    /**
     * @since 3.0.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @since 3.0.0
     */
    public function id(string $id): CurrencySwitcherSetting
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @since 3.0.0
     */
    public function exchangeRate(float $rate): CurrencySwitcherSetting
    {
        $this->exchangeRate = $rate;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }

    /**
     * @since 3.0.0
     */
    public function exchangeRateFractionDigits(int $exchangeRateFractionDigits): CurrencySwitcherSetting
    {
        $this->exchangeRateFractionDigits = $exchangeRateFractionDigits;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function getExchangeRateFractionDigits(): int
    {
        return $this->exchangeRateFractionDigits;
    }

    /**
     * @since 3.0.0
     */
    public function gateways(array $gateways): CurrencySwitcherSetting
    {
        $this->gateways = $gateways;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getGateways(): array
    {
        return $this->gateways;
    }
}
