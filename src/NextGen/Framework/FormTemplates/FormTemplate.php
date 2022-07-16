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
     * Returns the unique identifier of the template
     *
     * @unreleased
     */
    public function getId(): string
    {
        return static::id();
    }

    /**
     * Returns the human-readable name of the template
     *
     * @unreleased
     */
    public function getName(): string
    {
        return static::name();
    }

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
}
