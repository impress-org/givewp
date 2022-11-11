<?php

declare(strict_types=1);

namespace Give\Framework\Validation;

use Give\Framework\Validation\Rules\Max;
use Give\Framework\Validation\Rules\Min;
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
    ];

    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton(ValidationRulesRegister::class, function () {
            $register = new ValidationRulesRegister();

            foreach($this->validationRules as $rule) {
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
