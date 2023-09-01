<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

;

class PaymentGateways extends FormMigrationStep
{

    public function process()
    {
        $paymentGatewaysBlock = $this->fieldBlocks->findByName('givewp/payment-gateways');
        $paymentGatewaysBlock->setAttribute('stripeUseGlobalDefault', $this->formV2->getStripeUseGlobalDefault());
        $paymentGatewaysBlock->setAttribute('accountId', $this->formV2->getStripeAccountId());
    }
}
