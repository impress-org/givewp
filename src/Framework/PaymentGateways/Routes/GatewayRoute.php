<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\Contracts\OffsiteGatewayInterface;
use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;

/**
 * @unreleased
 */
class GatewayRoute
{
    /**
     * @unreleased
     *
     * @return void
     * @throws PaymentGatewayException
     */
    public function __invoke()
    {
        if ($this->isValidListener()) {
            $paymentGateways = give(PaymentGatewayRegister::class)->getPaymentGateways();
            $gatewayIds = array_keys($paymentGateways);

            if (!$this->isValidRequest($gatewayIds)) {
                throw new PaymentGatewayException('This route is not valid.');
            }

            $data = GatewayRouteData::fromRequest($_GET);

            /** @var PaymentGateway $gateway */
            $gateway = give($paymentGateways[$data->gatewayId]);

            $allowedGatewayMethods = array_filter($gateway::routeMethods, static function ($method) use ($data) {
                return $method === $data->gatewayMethod;
            });

            if (is_a($gateway, OffsiteGatewayInterface::class)) {
                $allowedGatewayMethods = array_merge(
                    $allowedGatewayMethods,
                    OffsiteGatewayInterface::defaultRouteMethods
                );
            }

            foreach ($allowedGatewayMethods as $gatewayMethod) {
                if (!method_exists($gateway, $gatewayMethod)) {
                    throw new PaymentGatewayException('The gateway method does not exist.');
                }
            }

            $gateway->handleGatewayRouteMethod($data->donationId, $data->gatewayMethod);
        }
    }

    /**
     * Check if the request is valid
     *
     * @unreleased
     *
     * @param  array  $gatewayIds
     *
     * @return bool
     * @example ?give-listener=give-gateway&give-gateway-id=test-gateway&give-donation-id=1&give-gateway-method=returnFromOffsiteRedirect
     *
     */
    private function isValidRequest($gatewayIds)
    {
        $isset = isset($_GET['give-gateway-id'], $_GET['give-gateway-method'], $_GET['give-donation-id']);
        $idValid = in_array($_GET['give-gateway-id'], $gatewayIds, true);

        return $isset && $idValid;
    }

    /**
     * Check if the listener is valid
     *
     * @unreleased
     *
     * @return bool
     */
    private function isValidListener()
    {
        return isset($_GET['give-listener']) && $_GET['give-listener'] === 'give-gateway';
    }
}
