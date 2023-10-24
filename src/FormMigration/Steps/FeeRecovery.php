<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

class FeeRecovery extends FormMigrationStep
{

    /**
     * @since 3.0.0
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

        if ($feeRecoverySettings['useGlobalSettings']) {
            $feeRecoverySettings = $this->getGlobalSettings();
        }

        $feeRecoveryBlock = BlockModel::make([
            'name' => 'givewp-fee-recovery/fee-recovery',
            'attributes' => $feeRecoverySettings,
        ]);
        $this->fieldBlocks->insertAfter('givewp/donation-amount', $feeRecoveryBlock);
    }

    /**
     * @since 3.0.0
     */
    private function getGlobalSettings(): array
    {
        return [
            'useGlobalSettings' => true,
            'feeSupportForAllGateways' => give_get_option('give_fee_configuration', 'all_gateways') === 'all_gateways',
            'perGatewaySettings' => [],
            'feePercentage' => (float)give_get_option('give_fee_percentage', 2.9),
            'feeBaseAmount' => (float)give_get_option('give_fee_base_amount', 0.30),
            'maxFeeAmount' => (float)give_get_option(
                'give_fee_maximum_fee_amount',
                give_format_decimal(['amount' => '0.00'])
            ),
            'includeInDonationSummary' => give_get_option('give_fee_breakdown', 'enabled') === 'enabled',
            'donorOptIn' => give_get_option('give_fee_mode', 'donor_opt_in') === 'donor_opt_in',
            'feeCheckboxLabel' => give_get_option(
                'give_fee_checkbox_label',
                __(
                    'I\'d like to help cover the transaction fees of {fee_amount} for my donation.',
                    'give-fee-recovery'
                )
            ),
            'feeMessage' => give_get_option(
                'give_fee_explanation',
                __('Plus an additional {fee_amount} to cover gateway fees.', 'give-fee-recovery')
            ),
        ];
    }
}
