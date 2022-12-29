<?php

declare(strict_types=1);

namespace Give\Framework\Validation;

use Give\Framework\Validation\Rules\Currency;
use Give\Framework\Validation\Rules\Email;
use Give\Framework\Validation\Rules\Integer;
use Give\Framework\Validation\Rules\Max;
use Give\Framework\Validation\Rules\Min;
use Give\Framework\Validation\Rules\Numeric;
use Give\Framework\Validation\Rules\Required;
use Give\Framework\Validation\Rules\Size;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;

class ServiceProvider implements ServiceProviderContract
{
    private $validationRules = [
        Required::class,
        Min::class,
        Max::class,
        Size::class,
        Numeric::class,
        Integer::class,
        Email::class,
        Currency::class,
    ];

    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton(ValidationRulesRegistrar::class, function () {
            $register = new ValidationRulesRegistrar();

            foreach ($this->validationRules as $rule) {
                $register->register($rule);
            }

            do_action('givewp_register_validation_rules', $register);

            return $register;
        });
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
    }
}
