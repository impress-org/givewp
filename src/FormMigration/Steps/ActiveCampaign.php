<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @since 3.10.0
 */
class ActiveCampaign extends FormMigrationStep
{
    /**
     * @since 3.10.0
     */
    public function canHandle(): bool
    {
        return $this->formV2->isActiveCampaignEnabled();
    }

    /**
     * @since 3.10.0
     */
    public function process(): void
    {
        $block = BlockModel::make([
            'name'       => 'give-activecampaign/activecampaign',
            'attributes' => $this->getAttributes()
        ]);

        $this->fieldBlocks->insertAfter('givewp/email', $block);
    }

    /**
     * @since 3.10.0
     */
    private function getAttributes(): array
    {
        return [
            'label'             => $this->formV2->getActiveCampaignLabel() ,
            'defaultChecked'    => $this->formV2->getActiveCampaignDefaultChecked(),
            'selectedLists'     => $this->formV2->getActiveCampaignSelectedLists(),
            'selectedTags'      => $this->formV2->getActiveCampaignTags()
        ];
    }
}
