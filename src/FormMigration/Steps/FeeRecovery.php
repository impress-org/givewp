<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

class FeeRecovery extends FormMigrationStep
{

    /**
     * @unreleased
     */
    public function process()
    {
        $feeRecoverySettings = $this->formV2->getFeeRecoverySettings();

        if (empty($feeRecoverySettings) || (
                $feeRecoverySettings['useGlobalSettings'] === true &&
                !give_is_setting_enabled(give_get_option('give_fee_recovery', 'disabled'))
            )) {
            return;
        }

        $feeRecoveryBlock = BlockModel::make([
            'name' => 'givewp-fee-recovery/fee-recovery',
            'attributes' => $feeRecoverySettings,
        ]);
        $this->fieldBlocks->insertAfter('givewp/donation-amount', $feeRecoveryBlock);
    }
}
