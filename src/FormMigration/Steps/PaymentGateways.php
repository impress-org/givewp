<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @since 3.0.0
 */
class PaymentGateways extends FormMigrationStep
{
    /**
     * @unreleased Add Per Form Gateways settings to this step
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

        $perFormGateways = give()->form_meta->get_meta($this->formV2->id, '_give_per_form_gateways', true);
        $compatibleGateways = array_keys(give_get_enabled_payment_gateways(0, 3));
        $gateways = [];
        if (is_array($perFormGateways) && count($perFormGateways) > 0) {

            foreach ($perFormGateways as $key => $checked) {
                if (in_array($key, $compatibleGateways)) {
                $gateways[] = [
                    'key' => $key,
                    'label' => give_get_gateway_checkout_label($key),
                    'checked' => (bool)$checked,
                ];
                }
            }
        }

        $paymentGatewaysBlock->setAttribute('useDefaultGateways', count($gateways) === 0);
        $paymentGatewaysBlock->setAttribute('perFormGateways', $gateways);
    }
}
