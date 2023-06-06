<?php

namespace Give\PHPStan\Log\Parameters;

use Give\Framework\Support\Facades\Str;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\ArrayType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

/**
 * Adds support for the \Give\Log\Log::$context parameter reflection to the MagicCallStaticMethodReflection
 *
 * @since 2.27.0
 */
class ContextParameterReflection implements ParameterReflection
{
    public function getName(): string
    {
        return 'context';
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function getType(): Type
    {
        return new ArrayType(
            new StringType(),
            new StringType()
        );
    }

    public function passedByReference(): PassedByReference
    {
        return PassedByReference::createNo();
    }

    public function isVariadic(): bool
    {
        return false;
    }

    public function getDefaultValue(): ?Type
    {
        return new ArrayType(
            new StringType(),
            new StringType()
        );
    }
}
