<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;
use Give\Log\Log;

/**
 * @since 3.11.0
 */
class ConvertKit extends FormMigrationStep
{
    /**
     * @since 3.11.0
     */
    public function canHandle(): bool
    {
        return $this->formV2->isConvertKitEnabled();
    }

    /**
     * @since 3.11.0
     */
    public function process(): void
    {
        $block = BlockModel::make([
            'name'       => 'givewp-convertkit/convertkit',
            'attributes' => $this->getAttributes()
        ]);

        $this->fieldBlocks->insertAfter('givewp/email', $block);
    }

    /**
     * @since 3.11.0
     */
    private function getAttributes(): array
    {
        return [
            'label'             => $this->formV2->getConvertKitLabel() ,
            'defaultChecked'    => $this->formV2->getConvertKitDefaultChecked(),
            'selectedForm'      => $this->formV2->getConvertKitSelectedForm(),
            'tagSubscribers'    => $this->formV2->getConvertKitTags()
        ];
    }
}
