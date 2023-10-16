<?php

namespace Give\PaymentGateways\Gateways\Offline\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Exceptions\Primitives\RuntimeException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\Gateways\Offline\OfflineGateway;

/**
 * Checks to see if the Offline gateway is disabled for the form and disables it if so. *
 *
 * @since 3.0.0
 */
class DisableGatewayWhenDisabledPerForm
{
    /**
     * @since 3.0.0
     *
     * @param array<PaymentGateway> $gateways
     *
     * @return array<PaymentGateway>
     */
    public function __invoke(array $gateways, int $formId): array
    {
        if (!array_key_exists(OfflineGateway::id(), $gateways)) {
            return $gateways;
        }

        $form = DonationForm::find($formId);

        $paymentGatewaysBlock = $form->blocks->findByName('givewp/payment-gateways');
        if (!$paymentGatewaysBlock) {
            throw new RuntimeException('Payment gateways block not found');
        }

        if (!$paymentGatewaysBlock->hasAttribute('offlineEnabled')) {
            return $gateways;
        }

        ['offlineEnabled' => $enabled] = $paymentGatewaysBlock->getAttributes();

        if (!$enabled) {
            unset($gateways[OfflineGateway::id()]);
        }

        return $gateways;
    }
}
