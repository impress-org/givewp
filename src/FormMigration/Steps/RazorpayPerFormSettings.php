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
    public function process()
    {
        $oldFormId = $this->formV2->id;

        $paymentGatewaysBlock = $this->fieldBlocks->findByName('givewp/payment-gateways');

        $paymentGatewaysBlock->setAttribute('useGlobalSettings',
            $this->getMetaValue($oldFormId, 'razorpay_per_form_account_options', 'global'));

        $paymentGatewaysBlock->setAttribute('liveKeyId',
            $this->getMetaValue($oldFormId, 'razorpay_per_form_live_merchant_key_id', ''));
        $paymentGatewaysBlock->setAttribute('liveSecretKey',
            $this->getMetaValue($oldFormId, 'razorpay_per_form_live_merchant_secret_key', ''));

        $paymentGatewaysBlock->setAttribute('testKeyId',
            $this->getMetaValue($oldFormId, 'razorpay_per_form_test_merchant_key_id', ''));
        $paymentGatewaysBlock->setAttribute('testSecretKey',
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
