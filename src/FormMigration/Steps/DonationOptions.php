<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

class DonationOptions extends FormMigrationStep
{
    public function process()
    {
        /** @var BlockModel $amountField */
        $amountField = $this->fieldBlocks->findByName('givewp/donation-amount');

        $priceOption = $this->getMetaV2('_give_price_option');
        $amountField->setAttribute('priceOption', $priceOption);

        if('set' === $priceOption) {
            $amountField->setAttribute('setPrice', $this->getMetaV2('_give_set_price'));
        }

        if('multi' === $priceOption) {
            // @note $formV2->levels only returns a single level
            $donationLevels = $this->getMetaV2('_give_donation_levels');

            $isDescriptionEnabled = false;
            $amountField->setAttribute('levels',
                array_map(function ($donationLevel) use (&$isDescriptionEnabled) {
                    $isDescriptionEnabled = $isDescriptionEnabled || ! empty($donationLevel['_give_text']);

                    return [
                        'value' => $this->roundAmount($donationLevel['_give_amount']),
                        'label' => $donationLevel['_give_text'],
                        'checked' => isset($donationLevel['_give_default']) && $donationLevel['_give_default'] === 'default',
                    ];
                }, $donationLevels)
            );
            $amountField->setAttribute('descriptionsEnabled', $isDescriptionEnabled);
        }

        $isCustomAmountEnabled = $this->formV2->isCustomAmountOptionEnabled();
        $amountField->setAttribute('customAmount', $isCustomAmountEnabled);

        if ($isCustomAmountEnabled) {
            $customAmountMin = $this->getMetaV2('_give_custom_amount_range_minimum');
            $customAmountMax = $this->getMetaV2('_give_custom_amount_range_maximum');

            if ($customAmountMin) {
                $amountField->setAttribute('customAmountMin', $this->roundAmount($customAmountMin));
            }

            if ($customAmountMax) {
                $amountField->setAttribute('customAmountMax', $this->roundAmount($customAmountMax));
            }
        }

        // @note No corresponding setting in v3 for "Custom Amount Text"
    }

    /**
     * @since 3.0.0
     */
    private function roundAmount($amount): float
    {
        return round((float)$amount, 2);
    }
}
