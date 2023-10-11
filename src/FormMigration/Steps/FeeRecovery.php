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
        $feeRecoveryBlock = $this->fieldBlocks->findByName('givewp-fee-recovery/fee-recovery');
        $feeRecoverySettings = $this->formV2->getFeeRecoverySettings();

        if (empty($feeRecoverySettings)) {
            if ($feeRecoveryBlock) {
                $this->fieldBlocks->remove($feeRecoveryBlock->name);
            }

            return;
        }

        if (!$feeRecoveryBlock) {
            $feeRecoveryBlock = BlockModel::make([
                'name' => 'givewp-fee-recovery/fee-recovery',
                'attributes' => [],
            ]);
            $this->fieldBlocks->insertAfter('givewp/donation-amount', $feeRecoveryBlock);
        }

        foreach ($feeRecoverySettings as $key => $value) {
            $feeRecoveryBlock->setAttribute($key, $value);
        }
    }
}
