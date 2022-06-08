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
                !$registeredGateway->supportsSubscriptions()
            ) {
                give_set_error(
                    'payment-gateway-not-support-subscription',
                    sprintf(
                    /* translators: 1. Payment gateway name.*/
                        esc_html__(
                            'Currently, we are not supporting recurring donations with %1$s.',
                            'give'
                        ),
                        $registeredGateway->getName()
                    )
                );
            }
        }
    }
}
