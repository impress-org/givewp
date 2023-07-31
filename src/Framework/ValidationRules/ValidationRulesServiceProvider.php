<?php

declare(strict_types=1);

namespace Give\Framework\ValidationRules;

use Give\Framework\ValidationRules\Rules\AllowedTypes;
use Give\ServiceProviders\ServiceProvider;
use Give\Vendors\StellarWP\Validation\ValidationRulesRegistrar;

class ValidationRulesServiceProvider implements ServiceProvider
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
        give(ValidationRulesRegistrar::class)->register(
            AllowedTypes::class
        );
    }
}
