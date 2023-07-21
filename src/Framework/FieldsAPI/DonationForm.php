<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Properties\DonationForm\CurrencySwitcherSetting;

/**
 * @unreleased
 */
class DonationForm extends Form {
    /**
     * @unreleased
     *
     * @var string
     */
    protected $defaultCurrency;

    /**
     * @unreleased
     *
     * @var CurrencySwitcherSetting[]
     */
    protected $currencySwitcherSettings = [];

    /**
     * @unreleased
     *
     * @var string
     */
    protected $currencySwitcherMessage;

    /**
     * @unreleased
     */
    public function currencySwitcherSettings(CurrencySwitcherSetting ...$settings): DonationForm
    {
        $this->currencySwitcherSettings = $settings;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getCurrencySwitcherSettings(): array
    {
        return $this->currencySwitcherSettings;
    }

    /**
     * @unreleased
     */
    public function currencySwitcherMessage(string $message): DonationForm
    {
        $this->currencySwitcherMessage = $message;

        return $this;
    }

    /**
     * @unreleased
     */
    public function defaultCurrency(string $currency): DonationForm
    {
        $this->defaultCurrency = $currency;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }
}