<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Properties\DonationForm\CurrencySwitcherSetting;

/**
 * @since 3.0.0
 */
class DonationForm extends Form {
    /**
     * @since 3.0.0
     *
     * @var string
     */
    protected $defaultCurrency;

    /**
     * @since 3.0.0
     *
     * @var CurrencySwitcherSetting[]
     */
    protected $currencySwitcherSettings = [];

    /**
     * @since 3.0.0
     *
     * @var string
     */
    protected $currencySwitcherMessage;

    /**
     * @since 3.0.0
     */
    public function currencySwitcherSettings(CurrencySwitcherSetting ...$settings): DonationForm
    {
        $this->currencySwitcherSettings = $settings;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function getCurrencySwitcherSettings(): array
    {
        return $this->currencySwitcherSettings;
    }

    /**
     * @since 3.0.0
     */
    public function currencySwitcherMessage(string $message): DonationForm
    {
        $this->currencySwitcherMessage = $message;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function defaultCurrency(string $currency): DonationForm
    {
        $this->defaultCurrency = $currency;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }
}