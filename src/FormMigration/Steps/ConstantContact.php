<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @unreleased
 */
class ConstantContact extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function canHandle(): bool
    {
        return $this->formV2->isConstantContactEnabled();
    }

    /**
     * @unreleased
     */
    public function process(): void
    {
        $block = BlockModel::make([
            'name'       => 'givewp/constantcontact',
            'attributes' =>[
                'label'     => $this->formV2->getConstantContactLabel(),
                'checked'   => $this->formV2->getConstantContactDefaultChecked(),
                'selectedEmailLists' => $this->formV2->getConstantContactSelectedLists(),
            ],
        ]);

        $this->fieldBlocks->insertAfter('givewp/email', $block);
    }
}
