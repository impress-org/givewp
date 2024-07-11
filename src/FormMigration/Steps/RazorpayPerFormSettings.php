<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @unreleased
 */
class RazorpayPerFormSettings extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function canHandle(): bool
    {
        return $this->formV2->isRazorpayPerFormSettingsEnabled();
    }

    /**
     * @unreleased
     */
    public function process()
    {
        $oldFormId = $this->formV2->id;

        $paymentGatewaysBlock = $this->fieldBlocks->findByName('givewp/payment-gateways');

        $paymentGatewaysBlock->setAttribute('razorpayUseGlobalSettings',
            $this->getMetaValue($oldFormId, 'razorpay_per_form_account_options', 'global') === 'global');

        $paymentGatewaysBlock->setAttribute('razorpayLiveKeyId',
            $this->getMetaValue($oldFormId, 'razorpay_per_form_live_merchant_key_id', ''));
        $paymentGatewaysBlock->setAttribute('razorpayLiveSecretKey',
            $this->getMetaValue($oldFormId, 'razorpay_per_form_live_merchant_secret_key', ''));

        $paymentGatewaysBlock->setAttribute('razorpayTestKeyId',
            $this->getMetaValue($oldFormId, 'razorpay_per_form_test_merchant_key_id', ''));
        $paymentGatewaysBlock->setAttribute('razorpayTestSecretKey',
            $this->getMetaValue($oldFormId, 'razorpay_per_form_test_merchant_secret_key', ''));
    }

    /**
     * @unreleased
     */
    private function getMetaValue(int $formId, string $metaKey, $defaultValue)
    {
        $metaValue = give()->form_meta->get_meta($formId, $metaKey, true);

        if ( ! $metaValue) {
            return $defaultValue;
        }

        return $metaValue;
    }
}
