<?php

namespace Give\Framework\Models\Contracts;

use Give\Framework\Models\Factories\ModelFactory;

/**
 * @since 2.19.6
 */
interface ModelHasFactory
{
    /**
     * @since 2.19.6
     *
     * @return ModelFactory
     */
    public static function factory();
}
