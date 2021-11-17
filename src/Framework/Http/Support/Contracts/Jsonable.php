<?php

namespace Give\Framework\Support\Contracts;

/**
 * @unreleased
 */
interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @unreleased
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}
