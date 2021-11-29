<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\DataTransferObjects\GatewayOffsiteReturnData;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;

/**
 * @unreleased
 */
class GatewayRouteFacade
{
    /**
     * @var array
     */
    protected $paymentGateways = [];
    /**
     * @var PaymentGatewayRegister
     */
    private $paymentGatewayRegister;

    /**
     * @param  PaymentGatewayRegister  $paymentGatewayRegister
     */
    public function __construct(PaymentGatewayRegister $paymentGatewayRegister)
    {
        $this->paymentGatewayRegister = $paymentGatewayRegister;
        $this->paymentGateways = $this->paymentGatewayRegister->getPaymentGateways();
    }

    /**
     * Register route with a matching gateway method
     *
     * @param  string  $gatewayMethod
     *
     * @return $this
     * @throws PaymentGatewayException
     */
    public function get($gatewayMethod)
    {
        if ($this->isValidListener()) {
            $gatewayIds = array_keys($this->paymentGateways);

            if (!$this->isValidRequest($gatewayIds, $gatewayMethod)) {
                throw new PaymentGatewayException('This route is not valid.');
            }

            $data = GatewayOffsiteReturnData::fromRequest($_GET);

            $gatewayClass = $this->paymentGateways[$data->gatewayId];

            if (!method_exists($gatewayClass, $gatewayMethod)) {
                throw new PaymentGatewayException('The gateway method does not exist.');
            }

            /** @var PaymentGateway $gateway */
            $gateway = give($gatewayClass);

            $method = $data->gatewayMethod;
            $gateway->$method($data->paymentId);
        }

        return $this;
    }

    /**
     * Specify the route is an offsite gateway
     *
     * @return $this
     */
    public function offsite()
    {
        $this->paymentGateways = $this->paymentGatewayRegister->getOffsitePaymentGateways();

        return $this;
    }

    /**
     * Check if the request is valid
     *
     * @unreleased
     *
     * @param  array  $gatewayIds
     * @param  string  $gatewayMethod
     *
     * @return bool
     * @example ?give-listener=give-gateway&give-gateway-id=test-gateway&give-gateway-method=handleReturnFromRedirect
     *
     */
    private function isValidRequest($gatewayIds, $gatewayMethod)
    {
        $isset = isset($_GET['give-gateway-id'], $_GET['give-gateway-method']);
        $idValid = in_array($_GET['give-gateway-id'], $gatewayIds, true);
        $methodValid = $_GET['give-gateway-method'] === $gatewayMethod;

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
