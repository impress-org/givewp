<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;

/**
 * @since 2.18.0
 */
class GatewayRoute
{
    /**
     * This is our entry point into the Gateway Routing system.
     *
     * @return void
     * @throws PaymentGatewayException
     * @since 2.18.0
     *
     */
    public function __invoke()
    {
        if ($this->isValidListener()) {
            /** @var PaymentGatewayRegister $paymentGatewaysRegister */
            $paymentGatewaysRegister = give(PaymentGatewayRegister::class);

            // get all registered gateways
            $paymentGateways = $paymentGatewaysRegister->getPaymentGateways();

            // get all registered gateway ids
            $gatewayIds = array_keys($paymentGateways);

            // make sure required params are valid
            if (!$this->isValidRequest($gatewayIds)) {
                throw new PaymentGatewayException('This route is not valid.');
            }

            // create DTO from GET request
            $data = GatewayRouteData::fromRequest($_GET);

            /**
             * Get the PaymentGateway instance
             *
             * @var PaymentGateway $gateway
             */
            $gateway = give($paymentGateways[$data->gatewayId]);

            // Make sure the method being called is defined in the gateway.
            if (
                !in_array($data->gatewayMethod, $gateway->routeMethods, true) ||
                !method_exists($gateway, $data->gatewayMethod)
            ) {
                throw new PaymentGatewayException('The gateway method does not exist.');
            }

            // Navigate to our payment gateway api to handle calling the gateway's method
            $gateway->handleGatewayRouteMethod($data->gatewayMethod, $data->queryParams);
        }
    }

    /**
     * Check if the request is valid
     *
     * @since 2.18.0
     *
     * @param  array  $gatewayIds
     *
     * @return bool
     * @example ?give-listener=give-gateway&give-gateway-id=test-gateway&give-donation-id=1&give-gateway-method=returnFromOffsiteRedirect
     *
     */
    private function isValidRequest($gatewayIds)
    {
        $isset = isset($_GET['give-gateway-id'], $_GET['give-gateway-method']);
        $idValid = in_array($_GET['give-gateway-id'], $gatewayIds, true);

        return $isset && $idValid;
    }

    /**
     * Check if the listener is valid
     *
     * @since 2.18.0
     *
     * @return bool
     */
    private function isValidListener()
    {
        return isset($_GET['give-listener']) && $_GET['give-listener'] === 'give-gateway';
    }
}
