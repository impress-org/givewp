<?php

namespace Give\Framework\PaymentGateways\Traits;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;

trait ResponseHelpers {
    /**
     * Handle Response
     *
     * @param  RedirectResponse|JsonResponse  $type
     * @since 2.18.0
     *
     */
    public function handleResponse($type)
    {
        if ($type instanceof RedirectResponse) {
            wp_redirect($type->getTargetUrl());
            exit;
        }

        if ($type instanceof JsonResponse) {
            wp_send_json(['data' => $type->getData()]);
        }
    }
}
