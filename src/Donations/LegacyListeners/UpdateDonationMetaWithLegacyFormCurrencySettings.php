<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @unreleased
 */
class UpdateDonationMetaWithLegacyFormCurrencySettings
{
    /**
     * @unreleased
     */
    public function __invoke(Donation $donation)
    {
        if (!isset($_POST['give-cs-exchange-rate'])) {
            return;
        }

        $exchangeRate = give_clean($_POST['give-cs-exchange-rate']) ?? '1';

        give_update_payment_meta($donation->id, '_give_cs_enabled', 'enabled');
        give_update_payment_meta($donation->id, '_give_cs_base_currency', give_get_option('currency'));

        /** @var Money $baseAmount */
        $baseAmount = $donation->amount->divide($exchangeRate);

        give_update_payment_meta($donation->id, '_give_cs_base_amount', $baseAmount->formatToDecimal());

        $donation->exchangeRate = $exchangeRate;
        $donation->save();
    }
}
