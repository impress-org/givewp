<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

class DonationOptions extends FormMigrationStep
{
    public function process()
    {
        $amountField = $this->fieldBlocks->findByName('givewp/donation-amount');

        $priceOption = $this->getMetaV2('_give_price_option');
        $amountField->setAttribute('priceOption', $priceOption);

        if('set' === $priceOption) {
            $amountField->setAttribute('setPrice', $this->getMetaV2('_give_set_price'));
        }

        if('multi' === $priceOption) {
            // @note $formV2->levels only returns a single level
            $donationLevels = $this->getMetaV2('_give_donation_levels');
            // @note No corresponding setting in v3 for `_give_text` for Donation Levels.
            $amountField->setAttribute('levels', wp_list_pluck($donationLevels, '_give_amount'));
        }

        if(give_is_setting_enabled($this->getMetaV2('_give_custom_amount'))) {
            $amountField->setAttribute('customAmountMin', $this->getMetaV2('_give_custom_amount_range_minimum'));
            $amountField->setAttribute('customAmountMax', $this->getMetaV2('_give_custom_amount_range_maximum'));
        }

        // @note No corresponding setting in v3 for "Custom Amount Text"
    }
}
