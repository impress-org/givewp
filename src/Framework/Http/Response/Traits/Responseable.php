<?php

namespace Give\Framework\Http\Response\Traits;

use Give\Framework\Http\Response\Response;
use Give\Framework\Http\Response\ResponseFactory;

use function Give\Framework\Http\Response\response;

/**
 * @unreleased
 */
trait Responseable {
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
        return response($content, $status, $headers);
    }
}