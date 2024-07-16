<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @since 3.14.0
 */
class RazorpayPerFormSettings extends FormMigrationStep
{
    /**
     * @since 3.14.0
     */
    public function canHandle(): bool
    {
        return $this->formV2->isRazorpayPerFormSettingsEnabled();
    }

    /**
     * @since 3.14.0
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
     * @since 3.14.0
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
