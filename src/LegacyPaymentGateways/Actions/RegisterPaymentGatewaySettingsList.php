<?php

namespace Give\LegacyPaymentGateways\Actions;

use Give\Framework\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayRegisterAdapter;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;

class RegisterPaymentGatewaySettingsList
{
    /**
     * Add gateways to settings list
     *
     * @since 2.18.0
     *
     * @param  array  $gatewayData
     */
    public function __invoke($gatewayData)
    {
        /** @var LegacyPaymentGatewayRegisterAdapter $legacyPaymentGatewayRegisterAdapter */
        $legacyPaymentGatewayRegisterAdapter = give(LegacyPaymentGatewayRegisterAdapter::class);

        /** @var PaymentGatewayRegister $paymentGatewayRegister */
        $paymentGatewayRegister = give(PaymentGatewayRegister::class);

        $newPaymentGateways = $paymentGatewayRegister->getPaymentGateways();

        if (!$newPaymentGateways) {
            return $gatewayData;
        }

        return $legacyPaymentGatewayRegisterAdapter->addNewPaymentGatewaysToLegacyListSettings(
            $gatewayData,
            $newPaymentGateways
        );
    }
}
