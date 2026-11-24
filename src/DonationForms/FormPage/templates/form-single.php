<?php
/**
 * @since TBD Rename $formId to $give_form_id: WordPress includes this template in the global scope.
 * @since 4.19.1 Escape output.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$give_form_id = (int) get_the_ID();

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block() output is the donation-form block's own markup, escaped internally by its render callback.
echo render_block(
    parse_blocks(
        '<!-- wp:givewp/donation-form {"formId":"' . $give_form_id . '","blockId":"' . $give_form_id . '"} /-->'
    )[0]
);

get_footer();
