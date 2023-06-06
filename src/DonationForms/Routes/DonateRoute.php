<?php

namespace Give\DonationForms\Routes;


use Give\DonationForms\Controllers\DonateController;
use Give\DonationForms\DataTransferObjects\DonateFormRouteData;
use Give\DonationForms\DataTransferObjects\DonateRouteData;
use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Log\Log;
use WP_Error;

/**
 * @since 0.1.0
 */
class DonateRoute
{
    use HandleHttpResponses;

    /**
     * @var DonateController
     */
    private $donateController;

    /**
     * @since 0.1.0
     *
     * @param  DonateController  $donateController
     */
    public function __construct(DonateController $donateController)
    {
        $this->donateController = $donateController;
    }

    /**
     * @since 0.1.0
     *
     * @return void
     */
    public function __invoke(array $request)
    {
        // create DTO from GET request
        $routeData = DonateRouteData::fromRequest(give_clean($_GET));

        // validate signature
        $this->validateSignature($routeData->routeSignature, $routeData);

        // create DTO from POST request
        $formData = DonateFormRouteData::fromRequest($request);

        try {
            $data = $formData->validated();

            $this->donateController->donate($data, $data->getGateway());
        } catch (DonationFormFieldErrorsException $exception) {
            $type = 'validation_error';
            $this->logError($type, $exception->getMessage(), $formData);
            $this->sendJsonError($type, $exception->getError());
        } catch (PaymentGatewayException $exception) {
            $type = 'gateway_error';
            $this->logError($type, $exception->getMessage(), $formData);
            $this->sendJsonError($type, new WP_Error($type, $exception->getMessage()));
        } catch (\Exception $exception) {
            $type = 'unknown_error';
            $this->logError($type, $exception->getMessage(), $formData);
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
     * @since 0.3.0
     */
    private function logError(
        string $type,
        string $exceptionMessage,
        DonateFormRouteData $formData
    ) {
        Log::error(
            "Donation Route Error: $type",
            [
                'error_type' => $type,
                'exceptionMessage' => $exceptionMessage,
                'formData' => $formData->toArray(),
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
