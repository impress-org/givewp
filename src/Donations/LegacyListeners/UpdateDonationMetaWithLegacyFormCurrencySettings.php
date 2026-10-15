<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 4.2.0
 */
class UpdateDonationMetaWithLegacyFormCurrencySettings
{
    /**
     * @since 4.2.0
     */
    public function __invoke(Donation $donation)
    {
        if (!isset($_POST['give-cs-exchange-rate']) || $_POST['give-cs-exchange-rate'] === '0') { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- optional Currency Switcher field of the donation form post; give_process_donation() verifies the form nonce before the donation is created.
            return;
        }

        $exchangeRate = give_clean($_POST['give-cs-exchange-rate']) ?? '1'; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- give_clean() unslashes and sanitizes with sanitize_text_field(), and drops serialized data; the donation form nonce is verified in give_process_donation().

        give_update_payment_meta($donation->id, '_give_cs_enabled', 'enabled');
        give_update_payment_meta($donation->id, '_give_cs_base_currency', give_get_option('currency'));

        /** @var Money $baseAmount */
        $baseAmount = $donation->amount->divide($exchangeRate);

        give_update_payment_meta($donation->id, '_give_cs_base_amount', $baseAmount->formatToDecimal());

        $donation->exchangeRate = $exchangeRate;
        $donation->save();
    }
}
