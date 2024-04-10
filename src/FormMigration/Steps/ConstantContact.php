<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @since 3.7.0
 */
class ConstantContact extends FormMigrationStep
{
    /**
     * @since 3.7.0
     */
    public function canHandle(): bool
    {
        return $this->formV2->isConstantContactEnabled();
    }

    /**
     * @since 3.7.0
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
