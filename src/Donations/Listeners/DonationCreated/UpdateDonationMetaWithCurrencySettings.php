<?php

namespace Give\Donations\Listeners\DonationCreated;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Properties\DonationForm\CurrencySwitcherSetting;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 4.2.0
 */
class UpdateDonationMetaWithCurrencySettings
{
    /**
     * @since 4.2.0
     *
     * @throws Exception
     */
    public function __invoke(Donation $donation)
    {
        if ($donation->amount->equals($donation->amountInBaseCurrency())) {
            return;
        }

        $form = DonationForm::find($donation->formId);

        if (!$form) {
            return;
        }

        $currencySwitcherSettings = $form->schema()->getCurrencySwitcherSettings();

        if (empty($currencySwitcherSettings)) {
            return;
        }

        /** @var CurrencySwitcherSetting|null $donorCurrencySetting */
        $donorCurrencySetting = current(
            array_filter(
                $currencySwitcherSettings,
                static function (CurrencySwitcherSetting $setting) use ($donation) {
                    return $setting->getId() === $donation->amount->getCurrency()->getCode();
                }
            )
        );

        if (!$donorCurrencySetting) {
            return;
        }

        give_update_payment_meta($donation->id, '_give_cs_enabled', 'enabled');

        give_update_payment_meta($donation->id, '_give_cs_base_currency', give_get_option('currency'));

        $donation->exchangeRate = (string)$donorCurrencySetting->getExchangeRate();
        $donation->save();


        // Note: this concept of base amount that currency switcher has been using, simply derives the base currency amount using division of the current exchange rate.
        // I believe this is different from $donation->amountInBaseCurrency();
        // The v2 logic can be found in the function `give_cs_store_switched_currency_meta_data`
        /** @var Money $baseAmount */
        $baseAmount = $donation->amount->divide($donorCurrencySetting->getExchangeRate());

        give_update_payment_meta($donation->id, '_give_cs_base_amount', $baseAmount->formatToDecimal());
    }
}
