<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\DataTransferObjects\GatewayOffsiteReturnData;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;

/**
 * @unreleased
 */
class GatewayRoute
{
    /**
     * @var string
     */
    protected $gatewayMethod;

    /**
     * @throws PaymentGatewayException
     */
    public function __invoke()
    {
        /** @var PaymentGatewayRegister $paymentGatewayRegister */
        $paymentGatewayRegister = give(PaymentGatewayRegister::class);
        $gateways = $paymentGatewayRegister->getOffsitePaymentGateways();
        $gatewayIds = array_keys($gateways);

        if ($this->isValidListener()) {
            if (!$this->isValidRequest($gatewayIds)) {
                throw new PaymentGatewayException('This route is not valid.');
            }

            $data = GatewayOffsiteReturnData::fromRequest($_GET);

            /** @var PaymentGateway $gateway */
            $gateway = give($gateways[$data->gatewayId]);

            $gatewayMethod = $data->gatewayMethod;
            $gateway->$gatewayMethod($data->paymentId);
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
     * @example ?give-listener=give-gateway&give-gateway-id=test-gateway&give-gateway-method=handleReturnFromRedirect
     *
     */
    private function isValidRequest($gatewayIds)
    {
        $isset = isset($_GET['give-gateway-id'], $_GET['give-gateway-method']);
        $idValid = in_array($_GET['give-gateway-id'], $gatewayIds, true);
        $methodValid = $_GET['give-gateway-method'] === $this->gatewayMethod;

        return $isset && $idValid && $methodValid;
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
