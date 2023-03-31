<?php

namespace Give\NextGen\DonationForm\Routes;


use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Log\Log;
use Give\NextGen\DonationForm\Controllers\DonateController;
use Give\NextGen\DonationForm\DataTransferObjects\DonateFormRouteData;
use Give\NextGen\DonationForm\DataTransferObjects\DonateRouteData;
use Give\NextGen\DonationForm\Exceptions\DonationFormFieldErrorsException;
use WP_Error;

/**
 * @since 0.1.0
 */
class DonateRoute
{
    use HandleHttpResponses;

    /**
     * @var PaymentGatewayRegister
     */
    private $paymentGatewayRegister;
    /**
     * @var DonateController
     */
    private $donateController;

    /**
     * @since 0.1.0
     *
     * @param  PaymentGatewayRegister  $paymentGatewayRegister
     * @param  DonateController  $donateController
     */
    public function __construct(PaymentGatewayRegister $paymentGatewayRegister, DonateController $donateController)
    {
        $this->paymentGatewayRegister = $paymentGatewayRegister;
        $this->donateController = $donateController;
    }

    /**
     * @since 0.1.0
     *
     * @return void
     *
     * @throws PaymentGatewayException
     */
    public function __invoke(array $request)
    {
        // create DTO from GET request
        $routeData = DonateRouteData::fromRequest(give_clean($_GET));

        // validate signature
        $this->validateSignature($routeData->routeSignature, $routeData);

        // create DTO from POST request
        $formData = DonateFormRouteData::fromRequest($request);

        // get all registered gateways
        $paymentGateways = $this->paymentGatewayRegister->getPaymentGateways();

        // get all registered gateway ids
        $gatewayIds = array_keys($paymentGateways);

        // make sure gateway is valid
        $this->validateGateway($formData->gatewayId, $gatewayIds);

        /** @var PaymentGateway $gateway */
        $gateway = give($paymentGateways[$formData->gatewayId]);

        try {
            $data = $formData->validated();

            $this->donateController->donate($data, $gateway);
        } catch (DonationFormFieldErrorsException $exception) {
            $type = 'validation_error';
            $this->logError($type, $exception->getMessage(), $formData, $gateway);
            $this->sendJsonError($type, $exception->getError());
        } catch (PaymentGatewayException $exception) {
            $type = 'gateway_error';
            $this->logError($type, $exception->getMessage(), $formData, $gateway);
            $this->sendJsonError($type, new WP_Error($type, $exception->getMessage()));
        } catch (\Exception $exception) {
            $type = 'unknown_error';
            $this->logError($type, $exception->getMessage(), $formData, $gateway);
            $this->sendJsonError($type, new WP_Error($type, $exception->getMessage()));
        }

        exit;
    }

    /**
     * @since 0.1.0
     *
     * @return void
     */
    private function validateSignature(string $routeSignature, DonateRouteData $data)
    {
        $signature = new DonateRouteSignature(
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
     * @since 0.1.0
     *
     * @return void
     * @throws PaymentGatewayException
     */
    private function validateGateway(string $paymentGateway, array $gatewayIds)
    {
        if (!in_array($paymentGateway, $gatewayIds, true)) {
            throw new PaymentGatewayException('This gateway is not valid.');
        }
    }

    /**
     * @unreleased
     */
    private function logError(
        string $type,
        string $exceptionMessage,
        DonateFormRouteData $formData,
        PaymentGateway $gateway
    ) {
        Log::error(
            "Donation Route Error: $type",
            [
                'error_type' => $type,
                'exceptionMessage' => $exceptionMessage,
                'formData' => $formData->toArray(),
                'gateway' => $gateway,
            ]
        );
    }

    /**
     * @param  string  $type
     * @param  array|string|WP_Error  $errors
     * @return void
     */
    protected function sendJsonError(string $type, WP_Error $errors)
    {
        wp_send_json_error([
            'type' => $type,
            'errors' => $errors,
        ]);
    }
}
