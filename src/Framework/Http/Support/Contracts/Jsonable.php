<?php

namespace Give\Framework\Support\Contracts;

/**
 * @since 2.18.0
 */
interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @since 2.18.0
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}
