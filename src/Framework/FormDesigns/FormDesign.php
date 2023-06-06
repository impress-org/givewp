<?php

namespace Give\Framework\FormDesigns;

use Give\Framework\FormDesigns\Contracts\FormDesignInterface;

/**
 * The FormDesign is meant to be extended to create custom GiveWP form designs.
 *
 * @since 0.1.0
 */
abstract class FormDesign implements FormDesignInterface
{
    protected $isMultiStep = false;

    /**
     * The unique identifier of the design
     *
     * @since 0.1.0
     */
    abstract public static function id(): string;

    /**
     * THe human-readable name of the design
     *
     * @since 0.1.0
     */
    abstract public static function name(): string;

    /**
     * Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
     *
     * @since 0.1.0
     *
     * @return string|false
     */
    public function css()
    {
        return false;
    }

    /**
     * Full URL of the script, or path of the script relative to the WordPress root directory.
     *
     * @since 0.1.0
     *
     * @return string|false
     */
    public function js()
    {
        return false;
    }

    /**
     * An array of dependencies compatible with the `$deps` parameter in wp_enqueue_script
     *
     * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-dependency-extraction-webpack-plugin/#wordpress
     *
     * @return array
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * @unreleased
     */
    public function isMultiStep(): bool
    {
        return $this->isMultiStep;
    }
}
