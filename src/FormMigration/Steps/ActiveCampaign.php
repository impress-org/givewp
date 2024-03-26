<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;
use Give\Log\Log;

/**
 * @unreleased
 */
class ActiveCampaign extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function canHandle(): bool
    {
        return $this->formV2->isActiveCampaignEnabled();
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    private function getAttributes(): array
    {
        return [
            'label'             => $this->formV2->getActiveCampaignLabel() ,
            'defaultChecked'    => $this->formV2->getActiveCampaignDefaultChecked(),
            'selectedLists'      => $this->formV2->getActiveCampaignSelectedLists(),
            'selectedTags'    => $this->formV2->getActiveCampaignTags()
        ];
    }
}
