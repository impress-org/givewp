<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;
use Give\Log\Log;

/**
 * @unreleased
 */
class ConvertKit extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function canHandle(): bool
    {
        return true;
    }

    /**
     * @unreleased
     */
    public function process(): void
    {
        $block = BlockModel::make([
            'name'       => 'givewp-convertkit/convertkit',
            'attributes' => $this->getAttributes()
        ]);

        Log::error('process', []);

        $this->fieldBlocks->insertAfter('givewp/email', $block);
    }

    /**
     * @unreleased
     */
    private function getAttributes(): array
    {
        Log::error('attributes', []);

        return [
            'label'             => $this->formV2->getConvertKitLabel() ??
                                   give_get_option('give_convertkit_label', __('Subscribe to newsletter?')),
            'defaultChecked'    => $this->formV2->getConvertKitDefaultChecked() ??
                                   give_is_setting_enabled(give_get_option('give_convertkit_checked_default')),
            'selectedForm'      => $this->formV2->getConvertKitSelectedForm() ??
                                   give_get_option('give_convertkit_list', []),
            'tagSubscribers'    => $this->formV2->getConvertKitTags() ??
                                   give_get_option('_give_convertkit_tags', []),
        ];
    }
}
