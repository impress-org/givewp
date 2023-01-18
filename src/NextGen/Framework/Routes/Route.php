<?php

namespace Give\NextGen\Framework\Routes;

use Give\Framework\Support\Facades\Facade;

/**
 * @unreleased
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