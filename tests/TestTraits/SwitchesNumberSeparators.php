<?php

namespace Give\Tests\TestTraits;

/**
 * Runs code with the number separators of another locale, the way a user locale of de_DE would set them.
 *
 * @since TBD
 */
trait SwitchesNumberSeparators
{
    /**
     * @since TBD
     *
     * @return mixed What the callback returns.
     */
    private function withSeparators(string $thousands, string $decimal, callable $callback)
    {
        global $wp_locale;

        $original = $wp_locale->number_format;
        $wp_locale->number_format = ['thousands_sep' => $thousands, 'decimal_point' => $decimal];

        try {
            return $callback();
        } finally {
            $wp_locale->number_format = $original;
        }
    }
}
