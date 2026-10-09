<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;

/**
 * @since 2.12.0
 */
class Factory
{

    /**
     * @since TBD Escape exception message.
     * @since 2.12.0
     *
     * @param string $type
     * @param        ...$args
     *
     * @return Node
     * @throws TypeNotSupported
     */
    public function make($type, ...$args)
    {
        $class = 'Give\\Framework\\FieldsAPI\\' . ucfirst($type);
        if ( ! class_exists($class)) {
            throw new TypeNotSupported(esc_html($type));
        }

        return new $class(...$args);
    }
}
