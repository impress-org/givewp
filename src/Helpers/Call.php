<?php

namespace Give\Helpers;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

class Call
{
    /**
     * Call an invokable class.
     *
     * @since 2.17.0
     *
     * @param string $class
     * @param mixed  $args
     *
     * @return mixed
     */
    public static function invoke($class, ...$args)
    {
        if ( ! method_exists($class, '__invoke')) {
            throw new InvalidArgumentException("This class is not invokable");
        }

        /** @var callable $instance */
        $instance = give($class);

        return $instance(...$args);
    }
}
