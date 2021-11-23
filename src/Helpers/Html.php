<?php

namespace Give\Helpers;

/**
 * HTML related helper functions
 *
 * @since 2.12.0
 */
class Html
{
    /**
     * A helper creating class attribute strings
     *
     * Note that no deduplication of class names takes place.
     *
     * Usage:
     *
     * ```php
     * Html::classNames(
     *     // Provide default class names
     *     'field-label',
     *     // Conditionally set class names
     *     [
     *         'fancy': $this->isFancy(),
     *         'hidden': $this->isHidden(),
     *     ],
     *     // This works the same providing them as individual arguments
     *     ['w-1/3', 'flex-grow', 'flex-shrink-0']
     * );
     * ```
     *
     * @param string|array ...$arguments
     *
     * @return string
     */
    public static function classNames(...$arguments)
    {
        $classList = [];

        array_walk_recursive(
            $arguments,
            static function ($value, $key) use (&$classList) {
                if (is_string($key) && $value === true) {
                    // In this case, a class name (the array key) is being set conditionally.
                    // We add the class name to the list if it passed the condition (the array value).
                    $classList[] = $key;
                } elseif (is_string($value)) {
                    $classList[] = $value;
                }
            }
        );

        return implode(' ', $classList);
    }
}
