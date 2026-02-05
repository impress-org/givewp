<?php

/**
 * Server-side render for The Giving Block donation form block.
 *
 * @unreleased
 *
 * @param array<string, string> $attributes Block attributes.
 * @return string Shortcode output.
 */
$display_type = $attributes['displayType'] ?? 'iframe';
$popup_button_text = $attributes['popupButtonText'] ?? '';

$shortcode = '[give_tgb_form type="' . esc_attr($display_type) . '"';
if ($popup_button_text !== '') {
    $shortcode .= ' popup_button_text="' . esc_attr($popup_button_text) . '"';
}
$shortcode .= ']';

echo do_shortcode($shortcode);
