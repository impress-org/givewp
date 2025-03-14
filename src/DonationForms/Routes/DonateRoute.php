<?php

namespace Give\DonationForms\Routes;


use Exception;
use Give\DonationForms\Controllers\DonateController;
use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\DonationForms\DataTransferObjects\DonateFormRouteData;
use Give\DonationForms\DataTransferObjects\DonateRouteData;
use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\DonationForms\Exceptions\DonationFormForbidden;
use Give\DonationForms\ValueObjects\DonationFormErrorTypes;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Log\Log;
use WP_Error;

/**
 * @since 3.0.0
 */
class DonateRoute
{
    use HandleHttpResponses;

    /**
     * @var DonateController
     */
    private $donateController;

    /**
     * @since 3.0.0
     *
     * @param  DonateController  $donateController
     */
    public function __construct(DonateController $donateController)
    {
        $this->donateController = $donateController;
    }

    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function __invoke(array $request)
    {
        // create DTO from GET request
        $routeData = DonateRouteData::fromRequest(give_clean($_GET));

        // validate signature
        $routeData->validateSignature();

        // create DTO from POST request
        $formData = DonateFormRouteData::fromRequest($request);

        try {
            $data = $formData->validated();

            /**
             * Allow for additional validation of the donation form data.
             * The donation flow can be interrupted by throwing an Exception.
             *
             * @since 3.15.0
             *
             * @param DonateControllerData $data
             */
            do_action('givewp_donate_form_data_validated', $data);

            $this->donateController->donate($data, $data->getGateway());
        } catch (DonationFormFieldErrorsException $exception) {
            $type = DonationFormErrorTypes::VALIDATION;
            $this->logError($type, $exception->getMessage(), $formData);
            $this->sendJsonError($type, $exception->getError());
        } catch (PaymentGatewayException $exception) {
            $type = DonationFormErrorTypes::GATEWAY;
            $this->logError($type, $exception->getMessage(), $formData);
            $this->sendJsonError($type, new WP_Error($type, $exception->getMessage()));
        } catch (DonationFormForbidden $exception) {
            wp_die($exception->getMessage(), 403);
        } catch (Exception $exception) {
            $type = DonationFormErrorTypes::UNKNOWN;
            $this->logError($type, $exception->getMessage(), $formData);
            $this->sendJsonError($type, new WP_Error($type, $exception->getMessage()));
        }

        exit;
    }

    /**
     * @since 3.0.0
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
