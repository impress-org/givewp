<?php

namespace Give\Framework\PaymentGateways\Traits;

use Exception;
use Give\DonationForms\ValueObjects\DonationFormErrorTypes;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use WP_Error;

trait HandleHttpResponses
{
    /**
     * Handle Response
     *
     * @since 2.32.0 added check for responding with json
     * @since 2.27.0 add support for json content-type
     * @since 2.18.0
     *
     * @param  RedirectResponse|JsonResponse  $type
     */
    public function handleResponse($type)
    {
        if ($type instanceof RedirectResponse) {
            if ($this->wantsJson()) {
                wp_send_json([
                    'type' => 'redirect',
                    'data' => [
                        'redirectUrl' => $type->getTargetUrl()
                    ]
                ]);
            }

            wp_redirect($type->getTargetUrl());
            exit;
        }

        if ($type instanceof JsonResponse) {
            wp_send_json(['data' => $type->getData()]);
        }
    }

    /**
     * Handle response on basis of request mode when exception occurs:
     * 1. Redirect to donation form if donation form submit.
     * 2. Return json response if processing payment on ajax.
     *
     * @since 3.0.0 Update response with type and WP_Error errors.
     * @since 2.21.0 Handle PHP exception.
     * @since 2.19.0
     */
    public function handleExceptionResponse(
        Exception $exception,
        string $message,
        string $type = DonationFormErrorTypes::UNKNOWN,
        WP_Error $errors = null
    ) {
        if ($exception instanceof PaymentGatewayException) {
            $message = $exception->getMessage();
            $type = DonationFormErrorTypes::GATEWAY;
        }

        if (wp_doing_ajax() || $this->wantsJson()) {
            $response = new JsonResponse($message);
            if (!$errors) {
                $errors = new WP_Error($type, $exception->getMessage());
            }

            // This structure works for v2 and v3 forms.
            wp_send_json_error([
                'type' => $type,
                'errors' => $errors,
                'data' => $response->getData()
            ]);
        }

        // This is for backwards compatibility with v2 forms.
        give_set_error('PaymentGatewayException', $message);
        give_send_back_to_checkout();
    }

    /**
     * This checks the server headers for 'application/json' to determine if it should respond with json.
     *
     * @since 2.32.0
     *
     * @return bool
     */
    protected function wantsJson(): bool
    {
        if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            return true;
        }

        return isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json');
    }
}
