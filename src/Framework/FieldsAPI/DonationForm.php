<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Properties\DonationForm\CurrencySwitcherSetting;

/**
 * @0.6.0
 */
class DonationForm extends Form {
    /**
     * @0.6.0
     *
     * @var string
     */
    protected $defaultCurrency;

    /**
     * @0.6.0
     *
     * @var CurrencySwitcherSetting[]
     */
    protected $currencySwitcherSettings = [];

    /**
     * @0.6.0
     *
     * @var string
     */
    protected $currencySwitcherMessage;

    /**
     * @0.6.0
     */
    public function currencySwitcherSettings(CurrencySwitcherSetting ...$settings): DonationForm
    {
        $this->currencySwitcherSettings = $settings;

        return $this;
    }

    /**
     * @0.6.0
     */
    public function getCurrencySwitcherSettings(): array
    {
        return $this->currencySwitcherSettings;
    }

    /**
     * @0.6.0
     */
    public function currencySwitcherMessage(string $message): DonationForm
    {
        $this->currencySwitcherMessage = $message;

        return $this;
    }

    /**
     * @0.6.0
     */
    public function defaultCurrency(string $currency): DonationForm
    {
        $this->defaultCurrency = $currency;

        return $this;
    }

    /**
     * @0.6.0
     */
    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }
}