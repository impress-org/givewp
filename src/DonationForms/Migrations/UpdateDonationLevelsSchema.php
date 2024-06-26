<?php

namespace Give\DonationForms\Migrations;

use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Update the donation levels schema to support descriptions
 *
 * @since 3.12.0
 */
class UpdateDonationLevelsSchema extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id()
    {
        return 'donation-forms-donation-levels-schema';
    }

    /**
     * @inheritdoc
     */
    public static function title()
    {
        return 'Update Donation Levels schema to support descriptions';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2024-04-22');
    }

    /**
     * @since 3.12.0
     */
    public function run()
    {
        $forms = give(DonationFormRepository::class)
            ->prepareQuery()
            ->whereIsNotNull("give_formmeta_attach_meta_fields.meta_value")
            ->getAll();

        if ( ! $forms) {
            return;
        }

        foreach ($forms as $form) {
            $amountBlock = $form->blocks->findByName('givewp/donation-amount');

            if ( ! $amountBlock) {
                continue;
            }

            $blockAttributes = $amountBlock->getAttributes();
            $levels = $blockAttributes["levels"];
            $defaultLevel = $blockAttributes["defaultLevel"] ?? 0;

            if ( ! is_array($levels) || empty($levels) || isset($levels[0]['value'])) {
                continue;
            }

            $levels = array_map(function($level) use ($defaultLevel) {
                return [
                    'value' => $level,
                    'label' => '',
                    'checked' => $level === $defaultLevel,
                ];
            }, $levels);

            $amountBlock->setAttribute('levels', $levels);
            $form->save();
        }
    }
}
