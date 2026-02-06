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
$popup_button_notice_enable = ! empty($attributes['popupButtonNoticeEnable']);
$popup_button_notice_short_text = $attributes['popupButtonNoticeShortText'] ?? '';
$popup_button_notice_long_text = $attributes['popupButtonNoticeLongText'] ?? '';
$popup_button_notice_short_cta = $attributes['popupButtonNoticeShortCta'] ?? '';

$shortcode = '[give_tgb_form type="' . esc_attr($display_type) . '"';
if ($popup_button_text !== '') {
    $shortcode .= ' popup_button_text="' . esc_attr($popup_button_text) . '"';
}
if ($popup_button_notice_enable) {
    $shortcode .= ' popup_button_notice_enable="1"';
    if ($popup_button_notice_short_text !== '') {
        $shortcode .= ' popup_button_notice_short_text="' . esc_attr($popup_button_notice_short_text) . '"';
    }
    if ($popup_button_notice_long_text !== '') {
        $shortcode .= ' popup_button_notice_long_text="' . esc_attr(str_replace(["\r\n", "\n", "\r"], ' ', $popup_button_notice_long_text)) . '"';
    }
    if ($popup_button_notice_short_cta !== '') {
        $shortcode .= ' popup_button_notice_short_cta="' . esc_attr($popup_button_notice_short_cta) . '"';
    }
}
$shortcode .= ']';

echo do_shortcode($shortcode);
