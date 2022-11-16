<?php

namespace Give\NextGen\Framework\FormDesigns\Contracts;

/**
 * The structure of a GiveWP FormDesign
 *
 * @unreleased
 */
interface FormDesignInterface
{
    /**
     * Return a unique identifier for the design
     *
     * @unreleased
     */
    public static function id(): string;

    /**
     * Returns a human-readable name for the design
     *
     * @unreleased
     */
    public static function name(): string;
}
