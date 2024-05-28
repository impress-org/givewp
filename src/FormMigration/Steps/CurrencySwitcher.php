<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @unreleased
 */
class CurrencySwitcher extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function process()
    {
        $status = $this->formV2->getCurrencySwitcherStatus();
        if (!$status) {
            return;
        }

        $currencySwitcherSettings = [];
        $currencySwitcherSettings['enable'] = $status;

        $message = $this->formV2->getCurrencySwitcherMessage();
        if ($message) {
            $currencySwitcherSettings['message'] = $message;
        }

        $defaultCurrency = $this->formV2->getCurrencySwitcherDefaultCurrency();
        if ($defaultCurrency) {
            $currencySwitcherSettings['defaultCurrency'] = $defaultCurrency;
        }

        $supportedCurrencies = $this->formV2->getCurrencySwitcherSupportedCurrencies();
        if ($supportedCurrencies) {
            $currencySwitcherSettings['supportedCurrencies'] = $supportedCurrencies;
        }

        $this->formV3->settings->currencySwitcherSettings = $currencySwitcherSettings;
    }
}
