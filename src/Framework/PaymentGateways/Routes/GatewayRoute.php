<?php

namespace Give\Framework\PaymentGateways\Routes;

use Exception;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Route\Route;

/**
 * @unreleased
 */
class GatewayRoute implements Route {
    /**
     * @var string
     */
    protected $gatewayMethod;

	/**
	 * @inheritDoc
	 */
	public function init() {
		add_action( 'wp', [ $this, 'handleRoute' ] );
	}

	/**
	 * @throws Exception
	 */
	public function handleRoute() {
        $gateways = give(PaymentGatewayRegister::class)->getPaymentGateways();
        $gatewayIds = array_keys($gateways);

		if ( $this->isValid($gatewayIds) ) {
            $gatewayId = $_GET['give-gateway-id'];
            $gatewayMethod = $_GET['give-gateway-method'];

            /** @var PaymentGateway $gateway */
            $gateway = give($gateways[$gatewayId]);

            $gateway->$gatewayMethod();
		}
	}

    /**
     * @unreleased
     *
     * @param array $gatewayIds
     *
     * @return bool
     * @example ?give-listener=give-gateway&give-gateway-id=test-gateway&give-gateway-action=handleReturnFromRedirect
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
