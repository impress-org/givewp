<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @since 3.3.0
 */
class FundsAndDesignations extends FormMigrationStep
{
    /**
     * @since 3.3.0
     */
    public function canHandle(): bool
    {
        return $this->formV2->hasFunds() || $this->formV2->hasFundOptions();
    }

    /**
     * @since 3.3.0
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
