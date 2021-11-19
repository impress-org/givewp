<?php

namespace Give\Shims;

use Give\ServiceProviders\ServiceProvider;

class ShimsServiceProvider implements ServiceProvider
{
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
        require_once 'blocks.php';
    }
}
