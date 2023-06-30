<?php

namespace Give\VendorOverrides\FieldConditions;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\ServiceProviders\ServiceProvider;
use Give\Vendors\StellarWP\FieldConditions\Config;

class FieldConditionsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritDoc}
     *
     * @since 2.27.3
     */
    public function register()
    {
        Config::setInvalidArgumentExceptionClass(InvalidArgumentException::class);
    }

    /**
     * {@inheritDoc}
     *
     * @since 2.27.3
     */
    public function boot()
    {
    }
}
