<?php

namespace Give\ServiceProviders;

use Give\Route\PayPalWebhooks;
use Give\Route\Route;

/**
 * Class Routes
 *
 * This loads the application routes. For now it's very simple, but in the future
 * we will introduce a unified Router. In the meantime, all routes are organized
 * here.
 */
class Routes implements ServiceProvider
{
    private $routes = [
        PayPalWebhooks::class,
    ];

    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        foreach ($this->routes as $route) {
            /** @var Route $route */
            $route = new $route();

            $route->init();
        }
    }
}
