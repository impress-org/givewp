<?php

/**
 * Server-side render for The Giving Block donation form block.
 *
 * @unreleased
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string Shortcode output.
 */
$display_type = $attributes['displayType'] ?? 'iframe';
echo do_shortcode('[give_tgb_form type="' . esc_attr($display_type) . '"]');
