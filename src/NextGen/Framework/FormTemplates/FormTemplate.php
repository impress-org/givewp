<?php

namespace Give\NextGen\Framework\FormTemplates;

use Give\NextGen\Framework\FormTemplates\Contracts\FormTemplateInterface;

/**
 * The FormTemplate is meant to be extended to create custom GiveWP form templates.
 *
 * @unreleased
 */
abstract class FormTemplate implements FormTemplateInterface {
    /**
     * The unique identifier of the template
     *
     * @unreleased
     */
    abstract public static function id(): string;

    /**
     * THe human-readable name of the template
     *
     * @unreleased
     */
    abstract public static function name(): string;

    /**
     * Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
     *
     * @unreleased
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
     * @unreleased
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
}
