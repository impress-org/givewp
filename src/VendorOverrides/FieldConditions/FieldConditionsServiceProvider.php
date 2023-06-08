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
     * @unreleased
     */
    public function register()
    {
        Config::setInvalidArgumentExceptionClass(InvalidArgumentException::class);
    }

    /**
     * {@inheritDoc}
     *
     * @unreleased
     */
    public function boot()
    {
    }
}
