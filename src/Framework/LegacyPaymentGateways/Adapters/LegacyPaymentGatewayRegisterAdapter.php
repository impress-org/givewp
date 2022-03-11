<?php

namespace Give\Framework\LegacyPaymentGateways\Adapters;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayAdapter;

class LegacyPaymentGatewayRegisterAdapter
{
    /**
     * Run the necessary legacy hooks on our LegacyPaymentGatewayAdapter
     * that prepares data to be sent to each gateway
     *
     * @since 2.19.0
     *
     * @param string $gatewayClass
     */
    public function connectGatewayToLegacyPaymentGatewayAdapter($gatewayClass)
    {
        /** @var LegacyPaymentGatewayAdapter $legacyPaymentGatewayAdapter */
        $legacyPaymentGatewayAdapter = give(LegacyPaymentGatewayAdapter::class);

        /** @var PaymentGatewayInterface $registeredGateway */
        $registeredGateway = give($gatewayClass);
        $registeredGatewayId = $registeredGateway->getId();

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
                $legacyPaymentGatewayAdapter->handleBeforeGateway($legacyPaymentData, $registeredGateway);
            }
        );
    }

    /**
     * Adds new payment gateways to legacy list for settings
     *
     * @since 2.19.0
     *
     * @param array $gatewaysData
     * @param array $newPaymentGateways
     *
     * @return array
     */
    public function addNewPaymentGatewaysToLegacyListSettings($gatewaysData, $newPaymentGateways)
    {
        foreach ($newPaymentGateways as $gatewayClassName) {
            /* @var PaymentGatewayInterface $paymentGateway */
            $paymentGateway = give($gatewayClassName);

            $gatewaysData[$paymentGateway->getId()] = [
                'admin_label' => $paymentGateway->getName(),
                'checkout_label' => $paymentGateway->getPaymentMethodLabel(),
            ];
        }

        return $gatewaysData;
    }
}
