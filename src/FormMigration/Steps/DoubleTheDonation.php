<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @since 3.8.0
 */
class DoubleTheDonation extends FormMigrationStep
{

    /**
     * @since 3.8.0
     */
    public function canHandle(): bool
    {
        return $this->formV2->getDoubleTheDonationStatus() === 'enabled';
    }

    /**
     * @since 3.8.0
     */
    public function process()
    {
        $block = BlockModel::make([
            'name'       => 'givewp/dtd',
            'attributes' => [
                'label'   => $this->formV2->getDoubleTheDonationLabel(),
                'company' => [
                    'company_id'   => '',
                    'company_name' => '',
                    'entered_text' => '',
                ],
            ],
        ]);

        $this->fieldBlocks->insertAfter('givewp/donation-amount', $block);
    }
}
