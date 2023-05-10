<?php

namespace Give\NextGen\DonationForm\Routes;


use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Log\Log;
use Give\NextGen\DonationForm\DataTransferObjects\DonateRouteData;
use Give\NextGen\DonationForm\DataTransferObjects\ValidationRouteData;
use Give\NextGen\DonationForm\Exceptions\DonationFormFieldErrorsException;
use WP_Error;

/**
 * @unreleased
 */
class ValidationRoute
{
    use HandleHttpResponses;

    /**
     * @unreleased
     */
    public function __invoke(array $request): bool
    {
        // create DTO from GET request
        $routeData = DonateRouteData::fromRequest(give_clean($_GET));

        // validate signature
        $this->validateSignature($routeData->routeSignature, $routeData);

        // create DTO from POST request
        $formData = ValidationRouteData::fromRequest($request);

        try {
            $response = $formData->validate();
            
            $this->handleResponse($response);
        } catch (DonationFormFieldErrorsException $exception) {
            $type = 'validation_error';
            $this->logError($type, $exception->getMessage(), $formData);
            $this->sendJsonError($type, $exception->getError());
        }

        exit;
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    private function logError(
        string $type,
        string $exceptionMessage,
        ValidationRouteData $formData
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
