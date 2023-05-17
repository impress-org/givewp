<?php

namespace Give\PHPStan\Log\Parameters;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

/**
 * Adds support for the \Give\Log\Log::$message parameter reflection to the MagicCallStaticMethodReflection
 *
 * @since 2.27.0
 */
class MessageParameterReflection implements ParameterReflection
{
    public function getName(): string
    {
        return 'message';
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function getType(): Type
    {
        return new StringType();
    }

    public function passedByReference(): PassedByReference
    {
        return PassedByReference::createNo();
    }

    public function isVariadic(): bool
    {
        return false;
    }

    public function getDefaultValue(): ?\PHPStan\Type\Type
    {
        return null;
    }
}
