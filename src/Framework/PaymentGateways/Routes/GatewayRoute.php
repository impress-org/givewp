<?php

namespace Give\Framework\PaymentGateways\Routes;

use Exception;
use Give\Framework\PaymentGateways\DataTransferObjects\GatewayOffsiteReturnData;
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
     * @throws Exception
     */
    public function __invoke()
    {
        $gateways = give(PaymentGatewayRegister::class)->getOffsitePaymentGateways();
        $gatewayIds = array_keys($gateways);

        if ($this->isValid($gatewayIds)) {
            $data = GatewayOffsiteReturnData::fromRequest($_GET);

            /** @var PaymentGateway $gateway */
            $gateway = give($gateways[$data->gatewayId]);

            $gatewayMethod = $data->gatewayMethod;
            $gateway->$gatewayMethod($data->paymentId);
        }
	}

    /**
     * @unreleased
     *
     * @param  array  $gatewayIds
     *
     * @return bool
     * @example ?give-listener=give-gateway&give-gateway-id=test-gateway&give-gateway-method=handleReturnFromRedirect
     *
     */
	private function isValid($gatewayIds) {
        $isset = isset($_GET['give-listener'], $_GET['give-gateway-id'], $_GET['give-gateway-method']);
        $listenerValid = $_GET['give-listener'] === 'give-gateway';
        $idValid = in_array($_GET['give-gateway-id'], $gatewayIds, true);
        $methodValid = $_GET['give-gateway-method'] === $this->gatewayMethod;

		return $isset && $listenerValid && $idValid && $methodValid;
	}
}
