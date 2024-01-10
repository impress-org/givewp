<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @unreleased
 */
class PerFormGateways extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function process()
    {
        $paymentGatewaysBlock = $this->fieldBlocks->findByName('givewp/payment-gateways');

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
