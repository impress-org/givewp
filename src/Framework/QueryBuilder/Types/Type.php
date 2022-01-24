<?php

namespace Give\Framework\QueryBuilder\Types;

use ReflectionClass;

/**
 * @unreleased
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
