<?php

namespace Give\Framework\Support\ValueObjects;

use BadMethodCallException;
use Give\Framework\Support\Facades\Str;

/**
 * @since 2.10.0
 */
abstract class Enum extends BaseEnum
{
    /**
     * @since 2.20.0
     *
     * Adds support for is{Value} methods. So if an Enum has an ACTIVE value, then an isActive() instance method is
     * automatically available.
     *
     * @param $name
     * @param $arguments
     *
     * @return bool
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'is') === 0) {
            $constant = Str::upper(Str::snake(Str::after($name, 'is')));

            if ( ! self::hasConstant($constant)) {
                throw new BadMethodCallException("$name does not match a corresponding enum constant.");
            }

            return $this->equals(parent::$constant());
        }

        throw new BadMethodCallException("Method $name does not exist on enum");
    }

    /**
     * @since 2.20.0
     */
    public function isOneOf(Enum...$enums): bool {
        foreach($enums as $enum) {
            if ( $this->equals($enum) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @since 2.20.0
     */
    public function getKeyAsCamelCase(): string
    {
        return Str::camel($this->getKey());
    }

    /**
     * @since 2.20.0
     */
    protected static function hasConstant(string $name): bool
    {
        return array_key_exists($name, static::toArray());
    }
}
