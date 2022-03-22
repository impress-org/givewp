<?php

namespace Give\Framework\Support\TypedProperties\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

class PropertyReadOnly extends Exception
{
    /**
     * @param string         $propertyName
     * @param string         $className
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($propertyName, $className, $code = 0, $previous = null)
    {
        parent::__construct(
            sprintf(
                'Property "%s" of class "%s" is read-only.',
                $propertyName,
                $className
            ),
            $code,
            $previous
        );
    }
}
