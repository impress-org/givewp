<?php

namespace Give\Framework\QueryBuilder\Types;

use ReflectionClass;

/**
 * @since 2.19.0
 */
abstract class Type
{
    /**
     * Get Defined Types
     *
     * @return array
     */
    public static function getTypes()
    {
        return (new ReflectionClass(static::class))->getConstants();
    }
}
