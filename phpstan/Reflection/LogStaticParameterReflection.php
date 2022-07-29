<?php

namespace Give\PHPStan\Reflection;

use PHPStan\Reflection\PassedByReference;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;

class LogStaticParameterReflection implements ParameterReflection
{
    public function getName(): string
    {
        return 'amount';
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function getType(): Type
    {
        return new IntegerType();
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
