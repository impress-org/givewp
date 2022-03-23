<?php

namespace Give\Framework\Support\ValueObjects;

use BadMethodCallException;
use Give\Framework\Support\Facades\Str;

/**
 * @method public getKeyAsCamelCase()
 */
class Enum extends \MyCLabs\Enum\Enum
{
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
     * @return string
     */
    public function getKeyAsCamelCase()
    {
        return Str::camel($this->getKey());
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected static function hasConstant($name)
    {
        return array_key_exists($name, static::toArray());
    }
}
