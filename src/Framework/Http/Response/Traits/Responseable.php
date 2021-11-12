<?php

namespace Give\Framework\Http\Response\Traits;

use Give\Framework\Http\Response\Response;
use Give\Framework\Http\Response\ResponseFactory;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;

use function Give\Framework\Http\Response\response;

/**
 * @unreleased
 */
trait Responseable
{
    /**
     * Return a new response from the application.
     *
     * @unreleased
     *
     * @param  string  $content
     * @param  int  $status
     * @param  array  $headers
     * @return Response|ResponseFactory
     */
    public function response($content = '', $status = 200, array $headers = [])
    {
        if (func_num_args() === 0) {
            return response();
        }

        return response($content, $status, $headers);
    }

    /**
     * Handle Response
     *
     * @unreleased
     *
     * @param  RedirectResponse|JsonResponse  $type
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