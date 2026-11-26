<?php

namespace Give\Framework\ListTable\Formatting;

use NumberFormatter;
use Throwable;

/**
 * The one way list tables write a count.
 *
 * @since TBD
 */
final class CountFormatter
{
    /**
     * Writes the number for the given locale, which is the browser's language when the list screen asks. With
     * no locale, or without the PHP intl extension, it is WordPress's own format.
     *
     * @since TBD
     *
     * @param int|float|string $value
     */
    public static function format($value, string $locale = ''): string
    {
        if ($locale !== '' && class_exists(NumberFormatter::class)) {
            try {
                $text = (new NumberFormatter($locale, NumberFormatter::DECIMAL))->format((float)$value);

                if ($text !== false) {
                    return $text;
                }
            } catch (Throwable $error) {
                // A locale the extension rejects falls back to the format below.
            }
        }

        return number_format_i18n((float)$value);
    }
}
