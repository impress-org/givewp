<?php

namespace Give\Framework\Http\Response;

namespace Illuminate\Contracts\Routing;

use Give\Framework\Http\Response\Response;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;

interface ResponseFactory
{
    /**
     * Create a new response instance.
     *
     * @param  string  $content
     * @param  int  $status
     * @param  array  $headers
     * @return Response
     */
    public function make($content = '', $status = 200, $headers = []);

    /**
     * Create a new JSON response instance.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return JsonResponse
     */
    public function json($data = [], $status = 200, $headers = [], $options = 0);

    /**
     * Create a new redirect response to the given path.
     *
     * @param  string  $path
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * @return RedirectResponse
     */
    public function redirectTo($path, $status = 302, $headers = [], $secure = null);
}
