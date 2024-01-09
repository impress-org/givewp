<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @unreleased
 */
class FundsAndDesignations extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function canHandle(): bool
    {
        return $this->formV2->hasFunds() || $this->formV2->hasFundOptions();
    }

    /**
     * @unreleased
     */
    public function process()
    {
        $fundsAndDesignationsAttributes = $this->formV2->getFundsAndDesignationsAttributes();

        $fundsAndDesignationsBlock = BlockModel::make([
            'name' => 'givewp/funds-and-designations',
            'attributes' => $fundsAndDesignationsAttributes,
        ]);
        $this->fieldBlocks->insertAfter('givewp/donation-amount', $fundsAndDesignationsBlock);
    }
}
