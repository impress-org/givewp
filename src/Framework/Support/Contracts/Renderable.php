<?php

namespace Give\Framework\Support\Contracts;

/**
 * @since 2.18.0
 */
interface Renderable
{
    /**
     * Get the evaluated contents of the object.
     *
     * @since 2.18.0
     *
     * @return string
     */
    public function render();
}
