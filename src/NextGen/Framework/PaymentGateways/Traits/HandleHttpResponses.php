<?php

namespace Give\NextGen\Framework\PaymentGateways\Traits;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;

/**
 * This is used to overwrite the same method for handling responses in GiveWP core within our Next Gen feature plugin.
 *
 * Eventually this will be removed and the core method will be used.
 */
trait HandleHttpResponses
{
    /**
     * Handle Response
     *
     * @unreleased add support for json content-type
     *
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
            wp_send_json([
                'type' => 'success',
                'data' => $type->getData()
            ]);
        }
    }
}
