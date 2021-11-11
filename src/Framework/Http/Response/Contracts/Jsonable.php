<?php

namespace Give\Framework\Http\Response\Contracts;

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
