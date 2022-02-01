<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Traits\ResponseHelpers;

use function Give\Framework\Http\Response\response;

/**
 * @since 2.18.0
 */
class GatewayRoute
{
    use ResponseHelpers;

    /**
     * This is our entry point into the Gateway Routing system.
     *
     * @return void
     * @throws PaymentGatewayException
     * @since 2.18.0
     * @unreleased - validate secureRouteMethods
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

            // get all public and secure gateway methods
            $allGatewayMethods = array_merge($gateway->routeMethods, $gateway->secureRouteMethods);

            // Make sure the method being called is defined in the gateway.
            if (
                !method_exists($gateway, $data->gatewayMethod) ||
                !in_array($data->gatewayMethod, $allGatewayMethods, true)
            ) {
                throw new PaymentGatewayException('The gateway method does not exist.');
            }

            // If method is in secureRouteMethods then, validate signature
            if (in_array($data->gatewayMethod, $gateway->secureRouteMethods, true)) {
                $this->validateSignature($data->routeSignature, $data);
            }

            // Navigate to our payment gateway api to handle calling the gateway's method
            $this->handleGatewayRouteMethod($gateway, $data->gatewayMethod, $data->queryParams);
        }
    }

    /**
     * Check if the request is valid
     *
     * @param  array  $gatewayIds
     *
     * @return bool
     * @since 2.18.0
     * @unreleased remove required check give-donation-id
     *
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

    /**
     * Validate signature using nonces
     *
     * @unreleased
     *
     * @param  string  $routeSignature
     * @param  GatewayRouteData  $data
     * @return void
     */
    private function validateSignature($routeSignature, GatewayRouteData $data)
    {
        $action = RouteSignature::make($data->gatewayId, $data->gatewayMethod, $data->queryParams);

        if (!wp_verify_nonce($routeSignature, $action->toString())) {
            wp_die('Forbidden', 403);
        }
    }

    /**
     * Handle gateway route method
     *
     * @since 2.18.0
     * @unreleased - replace $donationId with $queryParams array
     *
     * @param  PaymentGateway  $gateway
     * @param  string  $method
     * @param  array  $queryParams
     *
     * @return void
     *
     */
    private function handleGatewayRouteMethod(PaymentGateway $gateway, $method, $queryParams)
    {
        try {
            $this->handleResponse($gateway->$method($queryParams));
        } catch (PaymentGatewayException $paymentGatewayException) {
            $this->handleResponse(response()->json($paymentGatewayException->getMessage()));
        } catch (\Exception $exception) {
            PaymentGatewayLog::error($exception->getMessage());
            $this->handleResponse(
                response()->json(
                    __(
                        'An unexpected error occurred while processing your donation.  Please try again or contact us to help resolve.',
                        'give'
                    )
                )
            );
        }
    }
}
