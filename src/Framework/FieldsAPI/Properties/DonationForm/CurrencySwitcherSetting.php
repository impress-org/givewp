<?php

namespace Give\Framework\FieldsAPI\Properties\DonationForm;

use JsonSerializable;

/**
 * @unreleased
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
     * @unreleased
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
     * @unreleased
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @unreleased
     */
    public function id(string $id): CurrencySwitcherSetting
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @unreleased
     */
    public function exchangeRate(float $rate): CurrencySwitcherSetting
    {
        $this->exchangeRate = $rate;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }

    /**
     * @unreleased
     */
    public function exchangeRateFractionDigits(int $exchangeRateFractionDigits): CurrencySwitcherSetting
    {
        $this->exchangeRateFractionDigits = $exchangeRateFractionDigits;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getExchangeRateFractionDigits(): int
    {
        return $this->exchangeRateFractionDigits;
    }

    /**
     * @unreleased
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