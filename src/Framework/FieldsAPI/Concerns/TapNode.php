<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait TapNode
{
    /**
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
