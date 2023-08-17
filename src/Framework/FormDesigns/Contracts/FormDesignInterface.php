<?php

namespace Give\Framework\FormDesigns\Contracts;

/**
 * The structure of a GiveWP FormDesign
 *
 * @since 3.0.0
 */
interface FormDesignInterface
{
    /**
     * Return a unique identifier for the design
     *
     * @since 3.0.0
     */
    public static function id(): string;

    /**
     * Returns a human-readable name for the design
     *
     * @since 3.0.0
     */
    public static function name(): string;
}
