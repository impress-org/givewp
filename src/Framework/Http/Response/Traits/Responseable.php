<?php

namespace Give\Framework\Http\Response\Traits;

use Give\Framework\Http\Response\Response;
use Give\Framework\Http\Response\ResponseFactory;

trait Responseable {
     /**
     * Return a new response from the application.
     *
     * @param  string  $content
     * @param  int  $status
     * @param  array  $headers
     * @return Response|ResponseFactory
     */
    public function response($content = '', $status = 200, array $headers = [])
    {
        /** @var ResponseFactory $factory */
        $factory = give(ResponseFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($content, $status, $headers);
    }
}