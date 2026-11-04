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
     * @param string $html     The cell markup, already escaped.
     * @param string $fullText The plain text, for the title.
     */
    public static function render(string $html, string $fullText): string
    {
        return sprintf(
            '<span class="givewp-truncated-text" title="%s">%s</span>',
            esc_attr($fullText),
            $html
        );
    }
}
