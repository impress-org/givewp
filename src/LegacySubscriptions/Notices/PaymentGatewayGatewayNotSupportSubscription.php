<?php

namespace Give\LegacySubscriptions\Notices;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;

/**
 * @unreleased
 */
class PaymentGatewayGatewayNotSupportSubscription
{
    /**
     * @unreleased
     */
    public function __invoke(array $legacyDonationData)
    {
        $paymentGatewayRegister = give(PaymentGatewayRegister::class);
        $gatewayId = $legacyDonationData['gateway'];
        $legacyFormData = ['post_data' => give_clean($_POST)];

        if (array_key_exists($gatewayId, $paymentGatewayRegister->getPaymentGateways())) {
            $registeredGateway = give(PaymentGatewayRegister::class)->getPaymentGateway($gatewayId);

            if (
                give_recurring_is_donation_recurring($legacyFormData) &&
                (
                    !$registeredGateway->supportsSubscriptions() &&

                    // All gateways which registers with new gateway api do not have subscription module,
                    // But they can process subscription with legacy gateway api.
                    // This check will make sure that we don't show the notice for those gateways.
                    empty(\Give_Recurring::get_gateway_class($registeredGateway->getId()))
                )
            ) {
                give_set_error(
                    'payment-gateway-not-support-subscription',
                    sprintf(
                    /* translators: 1. Payment gateway name.*/
                        esc_html__(
                            'Recurring Donations are not supported with %1$s.',
                            'give'
                        ),
                        $registeredGateway->getPaymentMethodLabel()
                    )
                );
            }
        }
    }
}
