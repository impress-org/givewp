<?php

namespace Give\Framework\Support\ValueObjects;

/**
 * @method public getKeyAsCamelCase()
 */
class Enum extends \MyCLabs\Enum\Enum
{
    /**
     * @unreleased
     *
     * @param  string  $name
     * @return string
     */
    public static function camelCaseConstant($name)
    {
        return lcfirst(str_replace('_', '', ucwords(strtolower($name), '_')));
    }

    /**
     * @return string
     */
    public function getKeyAsCamelCase()
    {
        return static::camelCaseConstant($this->getKey());
    }
}

