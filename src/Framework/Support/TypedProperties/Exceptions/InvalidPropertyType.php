<?php

namespace Give\Framework\Support\TypedProperties\Exceptions;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\TypedProperties\TypedProperty;

class InvalidPropertyType extends InvalidArgumentException
{
    public function __construct($name, $type, $code = 0, $previous = null)
    {
        parent::__construct("Invalid type for property $name, must be type: $type", $code, $previous);
    }

    public static function fromTypedProperty(TypedProperty $property, $code = 0, $previous = null)
    {
        return new static($property->name, $property->type, $code, $previous);
    }
}
