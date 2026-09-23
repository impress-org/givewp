<?php
/**
 * @since TBD Escape output.
 */

get_header();

$formId = (int) get_the_ID();

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block() output is the donation-form block's own markup, escaped internally by its render callback.
echo render_block(
    parse_blocks(
        '<!-- wp:givewp/donation-form {"formId":"' . $formId . '","blockId":"' . $formId . '"} /-->'
    )[0]
);

get_footer();
