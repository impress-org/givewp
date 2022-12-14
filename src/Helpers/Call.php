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
     * @param mixed $args
     *
     * @return mixed
     *
     * @deprecated 2.23.0 Instantiate and invoke the class directly or use the (new Class())() syntax. This gives better tracking in the IDE.
     */
    public static function invoke(string $class, ...$args)
    {
        if (!method_exists($class, '__invoke')) {
            throw new InvalidArgumentException("{$class} class is not invokable");
        }

        /** @var callable $instance */
        $instance = give($class);

        return $instance(...$args);
    }
}
