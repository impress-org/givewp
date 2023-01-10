<?php

namespace Give\NextGen\Framework\Routes;

use Give\Framework\Support\Facades\Facade;

/**
 * @unreleased
 *
 * @method static Router get(string $uri, string|callable $action, string $method = '_invoke')
 * @method static Router post(string $uri, string|callable $action, string $method = '_invoke')
 * @method static Router url(string $uri): string
 */
class Route extends Facade {
    protected function getFacadeAccessor(): string
    {
        return Router::class;
    }
}