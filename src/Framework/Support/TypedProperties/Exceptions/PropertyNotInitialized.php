<?php

namespace Give\Framework\Support\TypedProperties\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Support\TypedProperties\TypedProperty;

class PropertyNotInitialized extends Exception
{
    /**
     * @param $propertyName
     * @param $code
     * @param $previous
     */
    public function __construct($propertyName, $code = 0, $previous = null)
    {
        parent::__construct("Typed property $propertyName must not be accessed before initialization", $code, $previous);
    }

    /**
     * @param TypedProperty $property
     *
     * @return static
     */
    public static function fromTypedProperty(TypedProperty $property)
    {
        return new static($property->name);
    }
}
