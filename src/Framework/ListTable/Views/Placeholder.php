<?php

namespace Give\Framework\ListTable\Views;

/**
 * The placeholder for a list table cell that has no value.
 *
 * @since TBD
 */
final class Placeholder
{
    /**
     * A dash that sighted users see and screen readers replace with the label.
     *
     * @since TBD
     *
     * @param string $label What is missing, for example "No amount".
     */
    public static function dash(string $label): string
    {
        return sprintf(
            '<span class="givewp-list-table-dash"><span aria-hidden="true">—</span><span class="screen-reader-text">%s</span></span>',
            esc_html($label)
        );
    }
}
