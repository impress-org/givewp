<?php

namespace Give\Framework\Models\Contracts;

use Give\Framework\Models\Factories\ModelFactory;

/**
 * @unreleased
 */
interface ModelHasFactory
{
    /**
     * @unreleased
     * 
     * @return ModelFactory
     */
    public static function factory();
}
