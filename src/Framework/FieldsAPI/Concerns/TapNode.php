<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @since 2.22.0
 */
trait TapNode
{
    /**
     * @since 2.22.0
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function tap(callable $callback)
    {
        call_user_func($callback, $this);

        return $this;
    }
}
