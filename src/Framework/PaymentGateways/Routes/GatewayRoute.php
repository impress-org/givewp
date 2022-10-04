<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;

use function Give\Framework\Http\Response\response;

/**
 * @since 2.18.0
 */
class GatewayRoute
{
    use HandleHttpResponses;

    /**
     * This is our entry point into the Gateway Routing system.
     *
     * @since 2.19.0 - validate secureRouteMethods
     * @since 2.18.0
     *
     * @return void
     *
     * @throws PaymentGatewayException
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
            $data = GatewayRouteData::fromRequest(give_clean($_GET));

            /**
             * Get the PaymentGateway instance
             *
             * @var PaymentGateway $gateway
             */
            $gateway = give($paymentGateways[$data->gatewayId]);

            if (!$gateway->supportsMethodRoute($data->gatewayMethod)) {
                throw new PaymentGatewayException('The gateway method does not exist.');
            }

            // If method is in secureRouteMethods then, validate signature
            if (in_array($data->gatewayMethod, $gateway->secureRouteMethods, true)) {
                $this->validateSignature($data->routeSignature, $data);
            }

            // Navigate to our payment gateway api to handle calling the gateway's method
            $this->handleGatewayRouteMethod($gateway, $data->gatewayMethod, $data->queryParams);

            exit;
        }
    }

    /**
     * Check if the request is valid
     *
     * @since 2.19.0 remove required check give-donation-id
     *
     * @since 2.18.0
     *
     * @param array $gatewayIds
     *
     * @return bool
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
     * @since 2.19.5 replace nonce with hash
     * @since 2.19.4 replace RouteSignature args with unique donationId
     * @since 2.19.0
     *
     * @param string $routeSignature
     * @param GatewayRouteData $data
     *
     * @return void
     */
    private function validateSignature($routeSignature, GatewayRouteData $data)
    {
        $signature = new RouteSignature(
            $data->gatewayId,
            $data->gatewayMethod,
            $data->routeSignatureId,
            $data->routeSignatureExpiration
        );

        if (!$signature->isValid($routeSignature)) {
            PaymentGatewayLog::error(
                'Invalid Secure Route',
                [
                    'routeSignature' => $routeSignature,
                    'signature' => $signature,
                    'signatureString' => $signature->toString(),
                    'signatureHash' => $signature->toHash(),
                    'signatureExpiration' => $signature->expiration,
                    'data' => $data
                ]
            );

            wp_die('Forbidden', 403);
        }
    }

    /**
     * Handle gateway route method
     *
     * @since 2.18.0
     *
     * @since 2.19.0 - replace $donationId with $queryParams array
     * @since 2.19.0 Record gateway id, callback method name and query params in log.
     *
     * @param PaymentGateway $gateway
     * @param string $method
     * @param array $queryParams
     */
    private function handleGatewayRouteMethod(PaymentGateway $gateway, $method, $queryParams)
    {
        try {
            $this->handleResponse($gateway->callRouteMethod($method, $queryParams));
        } catch (PaymentGatewayException $paymentGatewayException) {
            $this->handleResponse(response()->json($paymentGatewayException->getMessage()));
        } catch (\Exception $exception) {
            PaymentGatewayLog::error(
                $exception->getMessage(),
                [
                    'Payment Gateway' => $gateway::id(),
                    'Payment Gateway Method' => $method,
                    'Query Params' => $queryParams
                ]
            );
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
