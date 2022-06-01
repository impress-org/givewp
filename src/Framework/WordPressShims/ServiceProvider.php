<?php

declare(strict_types=1);

namespace Give\Framework\WordPressShims;

use Give\ServiceProviders\ServiceProvider as BaseServiceProvider;

class ServiceProvider implements BaseServiceProvider
{
    /**
     * Autoload all the shim files in the same directory as this file.
     *
     * @since 2.20.0
     *
     * @inheritDoc
     */
    public function register()
    {
        $files = glob(__DIR__ . '/*.php');
        foreach ($files as $file) {
            require_once $file;
        }
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
    }
}
