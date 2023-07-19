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
     * @unreleased
     */
    public function __construct(string $id, float $exchangeRate = 0, array $gateways = [])
    {
        $this->id = $id;
        $this->exchangeRate = $exchangeRate;
        $this->gateways = $gateways;
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
    public function exchangeRate(float $rate): CurrencySwitcherSetting
    {
        $this->exchangeRate = $rate;

        return $this;
    }

    /**
     * @unreleased
     */
    public function gateways(array $gateways): CurrencySwitcherSetting
    {
        $this->gateways = $gateways;

        return $this;
    }
}