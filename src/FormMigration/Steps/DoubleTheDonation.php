<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @unreleased
 */
class DoubleTheDonation extends FormMigrationStep
{

    /**
     * @unreleased
     */
    public function process()
    {
        if ($this->formV2->getDoubleTheDonationStatus() !== 'enabled') {
            return;
        }

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
