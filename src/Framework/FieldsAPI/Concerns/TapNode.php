<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 */
trait TapNode
{
    /**
     * @unreleased
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
