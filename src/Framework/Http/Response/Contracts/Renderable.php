<?php

namespace Give\Framework\Http\Response\Contracts;

/**
 * @unreleased
 */
interface Renderable
{
    /**
     * Get the evaluated contents of the object.
     *
     * @unreleased
     *
     * @return string
     */
    public function render();
}
