<?php

namespace Give\Framework\LegacyPaymentGateways\Adapters;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayAdapter;

use function method_exists;

class LegacyPaymentGatewayRegisterAdapter
{
    /**
     * Run the necessary legacy hooks on our LegacyPaymentGatewayAdapter
     * that prepares data to be sent to each gateway
     *
     * @since 2.19.0
     */
    public function connectGatewayToLegacyPaymentGatewayAdapter(string $gatewayClass)
    {
        /** @var LegacyPaymentGatewayAdapter $legacyPaymentGatewayAdapter */
        $legacyPaymentGatewayAdapter = give(LegacyPaymentGatewayAdapter::class);

        /** @var PaymentGatewayInterface $registeredGateway */
        $registeredGateway = give($gatewayClass);
        $registeredGatewayId = $registeredGateway::id();

        add_action(
            "give_{$registeredGatewayId}_cc_form",
            static function ($formId, $args) use ($registeredGateway, $legacyPaymentGatewayAdapter) {
                echo $legacyPaymentGatewayAdapter->getLegacyFormFieldMarkup($formId, $args, $registeredGateway);
            },
            10,
            2
        );

        add_action(
            "give_gateway_{$registeredGatewayId}",
            static function ($legacyPaymentData) use ($registeredGateway, $legacyPaymentGatewayAdapter) {
                $legacyPaymentGatewayAdapter->handleBeforeGateway(give_clean($legacyPaymentData), $registeredGateway);
            }
        );
    }

    /**
     * Adds new payment gateways to legacy list for settings
     *
     * @since 2.25.0 add is_visible key to $gatewayData
     * @since 2.19.0
     */
    public function addNewPaymentGatewaysToLegacyListSettings(array $gatewaysData, array $newPaymentGateways): array
    {
        foreach ($newPaymentGateways as $gatewayClassName) {
            /* @var PaymentGatewayInterface $paymentGateway */
            $paymentGateway = give($gatewayClassName);

            $gatewaysData[$paymentGateway::id()] = [
                'admin_label' => $paymentGateway->getName(),
                'checkout_label' => $paymentGateway->getPaymentMethodLabel(),
                'is_visible' => $this->supportsLegacyForm($paymentGateway),
            ];
        }

        return $gatewaysData;
    }

    /**
     * @since 2.25.0
     */
    public function supportsLegacyForm(PaymentGatewayInterface $gateway): bool
    {
        return method_exists($gateway, 'supportsLegacyForm') ? $gateway->supportsLegacyForm() : true;
    }
}
