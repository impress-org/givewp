<?php

namespace Give\WPCom;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider;
use Give\WPCom\Hooks\MarketPlaceResponseHandler;

class WPComServiceProvider implements ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        Hooks::addFilter('wpcom_marketplace_webhook_response_acme_product', MarketPlaceResponseHandler::class, '__invoke', 10, 3);
    }
}
