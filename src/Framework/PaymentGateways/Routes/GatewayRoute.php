<?php

namespace Give\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\Actions\GenerateGatewayRouteUrl;
use Give\Framework\PaymentGateways\Actions\ProcessOffsitePaymentRedirectOnGatewayRoute;
use Give\Framework\PaymentGateways\Contracts\OffsiteGatewayInterface;
use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Helpers\Gateway;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;

/**
 * @since 2.18.0
 *
 * // Todo validate donation id before processing gateway method
 * // Todo match gateway id with donation payment gateway id processing gateway method
 */
class GatewayRoute
{
    /**
     * @throws PaymentGatewayException
     */
    public function __invoke()
    {
        if ($this->isValidListener()) {
            $data = GatewayRouteData::fromRequest($_GET);
            $this->process($data);
        }
    }

    /**
     * @since 2.18.0
     *
     * @return void
     * @throws PaymentGatewayException
     */
    public function process(GatewayRouteData $data)
    {
        /** @var PaymentGatewayRegister $paymentGatewaysRegister */
        $paymentGatewaysRegister = give(PaymentGatewayRegister::class);
        $paymentGateways = $paymentGatewaysRegister->getPaymentGateways();
        $gatewayIds = array_keys($paymentGateways);

        if ( ! $this->isValidRequest($gatewayIds, $data->gatewayId)) {
            throw new PaymentGatewayException('This route is not valid.');
        }

        if ( ! $this->hasValidNonce($data)) {
            throw new PaymentGatewayException('This route does not have valid nonce.');
        }

        /** @var PaymentGateway $gateway */
        $gateway = give($paymentGateways[$data->gatewayId]);

        $allowedGatewayMethods = Gateway::isOffsitePaymentGateway($gateway) ? $gateway->routeMethods : [];

        if (is_a($gateway, OffsiteGatewayInterface::class)) {
            $allowedGatewayMethods = array_merge(
                $allowedGatewayMethods,
                OffsiteGatewayInterface::defaultRouteMethods
            );
        }

        if (
            ! in_array($data->gatewayMethod, $allowedGatewayMethods, true) ||
            ! method_exists($gateway, $data->gatewayMethod)
        ) {
            throw new PaymentGatewayException('The gateway method does not exist.');
        }

        /**
         * Gateway route can be used for:
         * 1. Webhooks
         * 2. Offsite payment gateway redirect
         *
         * Webhooks controller mostly need to return http status 200 or other, so no need to involve core logic.
         * Payment gateway can handle it.
         *
         * Offsite payment gateway redirect further need redirect to success or failed or cancelled donation page.
         * For this reason we need core to involve to handle redirect.
         */
        if (in_array($data->gatewayMethod, OffsiteGatewayInterface::defaultRouteMethods, true)) {
            (new ProcessOffsitePaymentRedirectOnGatewayRoute($gateway))
                ->handleGatewayRouteMethod($data->donationId, $data->gatewayMethod);
        } else {
            $gateway->handleGatewayRouteMethod($data->donationId, $data->gatewayMethod);
        }

        exit();
    }

    /**
     * Check if the request is valid
     *
     * @since 2.18.0
     *
     * @param array $registeredGatewayIds
     *
     * @return bool
     * @example ?give-listener=give-gateway&give-gateway-id=test-gateway&give-donation-id=1&give-gateway-method=returnFromOffsiteRedirect
     *
     */
    private function isValidRequest($registeredGatewayIds, $gatewayId)
    {
        return in_array($gatewayId, $registeredGatewayIds, true);
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
     * Returns whether nonce is valid.
     *
     * @unreleased
     *
     * @since 2.18.0
     *
     * @return bool
     */
    private function hasValidNonce(GatewayRouteData $data)
    {
        // Few gateway route like offsite payment gateway redirect url will contain nonce
        // Which we need to verify for security purpose.
        if (null !== $data->nonce) {
            return wp_verify_nonce(
                $data->nonce,
                (new GenerateGatewayRouteUrl())->getNonceActionName($data)
            );
        }

        // We can return true for other gateway route urls like webhook, paypal ipn
        // Because these routes will not contain nonce.
        return true;
    }
}
