<?php

namespace Give\Framework\Routes;

use Give\Framework\Support\Facades\Facade;

/**
 * @since 3.0.0
 *
 * @method static string get(string $uri, string|callable $action, string $method = '_invoke')
 * @method static void post(string $uri, string|callable $action, string $method = '_invoke')
 * @method static string url(string $uri, array $args = [])
 */
class Route extends Facade {
    protected function getFacadeAccessor(): string
    {
        return Router::class;
    }
}