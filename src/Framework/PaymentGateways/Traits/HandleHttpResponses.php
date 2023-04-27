<?php

namespace Give\Framework\PaymentGateways\Traits;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;

trait HandleHttpResponses
{
    /**
     * Handle Response
     *
     * @since 2.27.0 add support for json content-type
     * @since 2.18.0
     *
     * @param  RedirectResponse|JsonResponse  $type
     */
    public function handleResponse($type)
    {
        if ($type instanceof RedirectResponse) {
            if (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], "application/json")) {
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
     * @since 2.21.0 Handle PHP exception.
     * @since 2.19.0
     */
    public function handleExceptionResponse(\Exception $exception, string $message)
    {
        if ($exception instanceof PaymentGatewayException) {
            $message = $exception->getMessage();
        }

        if (wp_doing_ajax()) {
            $response = new JsonResponse($message);

            $this->handleResponse($response);
        }

        give_set_error('PaymentGatewayException', $message);
        give_send_back_to_checkout();
    }
}
