<?php

namespace Give\Framework\Http\Response\Contracts;

use Give\Framework\Http\Response\Response;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;

/**
 * @unreleased
 */
interface ResponseFactoryInterface
{
    /**
     * Create a new response instance.
     *
     * @unreleased
     *
     * @param  string  $content
     * @param  int  $status
     * @param  array  $headers
     * @return Response
     */
    public function make($content = '', $status = 200, array $headers = []);

    /**
     * Create a new "no content" response.
     *
     * @unreleased
     *
     * @param  int  $status
     * @param  array  $headers
     * @return Response
     */
    public function noContent($status = 204, array $headers = []);

    /**
     * Create a new JSON response instance.
     *
     * @unreleased
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return JsonResponse
     */
    public function json($data = [], $status = 200, array $headers = [], $options = 0);

    /**
     * Create a new JSONP response instance.
     *
     * @unreleased
     *
     * @param  string  $callback
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return JsonResponse
     */
    public function jsonp($callback, $data = [], $status = 200, array $headers = [], $options = 0);

    /**
     * Create a new redirect response to the given path.
     *
     * @unreleased
     *
     * @param  string  $path
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * @return RedirectResponse
     */
    public function redirectTo($path, $status = 302, $headers = [], $secure = null);
}
