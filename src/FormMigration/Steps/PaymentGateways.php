<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @since 3.0.0
 */
class PaymentGateways extends FormMigrationStep
{
    /**
     * @since 3.0.0
     */
    public function process()
    {
        $paymentGatewaysBlock = $this->fieldBlocks->findByName('givewp/payment-gateways');

        // Stripe settings
        $paymentGatewaysBlock->setAttribute('stripeUseGlobalDefault', $this->formV2->getStripeUseGlobalDefault());
        $paymentGatewaysBlock->setAttribute('accountId', $this->formV2->getStripeAccountId());

        // Offline settings
        $attributes = $this->formV2->getOfflineAttributes();

        foreach ($attributes as $key => $value) {
            $paymentGatewaysBlock->setAttribute($key, $value);
        }
    }
}
