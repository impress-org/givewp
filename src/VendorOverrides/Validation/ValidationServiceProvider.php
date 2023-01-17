<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Validation;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;
use Give\VendorOverrides\Validation\Exceptions\ValidationException;
use Give\Vendors\StellarWP\Validation\Config;

class ValidationServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        Config::setServiceContainer(give()->getContainer());
        Config::setHookPrefix('givewp_');
        Config::setInvalidArgumentExceptionClass(InvalidArgumentException::class);
        Config::setValidationExceptionClass(ValidationException::class);
        Config::initialize();
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
    }
}
