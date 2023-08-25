<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

;

class PaymentGateways extends FormMigrationStep
{

    public function process()
    {
        $paymentGatewaysBlock = $this->fieldBlocks->findByName('givewp/payment-gateways');
        $paymentGatewaysBlock->setAttribute('gatewaysSettings', [
            'stripe_payment_element' => [
                'useGlobalDefault' => $this->formV2->getStripeUseGlobalDefault(),
                'accountId' => $this->formV2->getStripeAccountId(),
            ]
        ]);
    }
}
