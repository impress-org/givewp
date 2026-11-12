<?php

namespace Give\Framework\ListTable\Views;

/**
 * Wraps long cell text so the table clamps it to two lines and the full text stays one hover away.
 *
 * @since TBD
 */
final class TruncatedText
{
    /**
     * The full text stays in the markup, so screen readers read all of it. The title shows it on hover.
     *
     * @since TBD
     *
     * @param string $escapedHtml The cell markup. It is printed as given, so escape it before the call.
     * @param string $fullText    The plain text, for the title. It is escaped here.
     */
    public static function render(string $escapedHtml, string $fullText): string
    {
        return sprintf(
            '<span class="givewp-truncated-text" title="%s">%s</span>',
            esc_attr($fullText),
            $escapedHtml
        );
    }
}
