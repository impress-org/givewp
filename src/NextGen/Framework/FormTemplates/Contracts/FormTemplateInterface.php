<?php

namespace Give\NextGen\Framework\FormTemplates\Contracts;

/**
 * The structure of a GiveWP FormTemplate
 *
 * @unreleased
 */
interface FormTemplateInterface
{
    /**
     * Return a unique identifier for the template
     *
     * @unreleased
     */
    public static function id(): string;

    /**
     * Returns a human-readable name for the template
     *
     * @unreleased
     */
    public static function name(): string;
}
