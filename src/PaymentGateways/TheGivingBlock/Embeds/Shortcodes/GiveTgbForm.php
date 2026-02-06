<?php

namespace Give\PaymentGateways\TheGivingBlock\Embeds\Shortcodes;

use Give\PaymentGateways\TheGivingBlock\DataTransferObjects\Organization;
use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * Shortcode handler for [give_tgb_form] (iframe or popup donation form).
 *
 * @unreleased
 */
class GiveTgbForm
{
    /**
     * @unreleased
     */
    public function renderShortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'type' => 'iframe',
            'popup_button_text' => '',
            'popup_button_notice_enable' => '',
            'popup_button_notice_short_text' => '',
            'popup_button_notice_long_text' => '',
            'popup_button_notice_short_cta' => '',
        ], $atts, 'give_tgb_form');

        if (!OrganizationRepository::isConnected()) {
            return '<div class="give-tgb-error" style="background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0;">' .
                   '<strong>' . __('The Giving Block plugin by GiveWP is not configured.', 'give') . '</strong><br>' .
                   __('To display donation forms, you need to configure your organization in the plugin settings first.', 'give') .
                   '</div>';
        }

        $organization = Organization::fromOptions();
        $widgetCode = $organization->widgetCode ?? [];

        if ($atts['type'] === 'popup') {
            return $this->renderModalButton($widgetCode, $atts);
        }

        return $this->renderFormIframe($widgetCode);
    }

    /**
     * @unreleased
     *
     * @param array<string, string> $widgetCode
     */
    private function renderFormIframe(array $widgetCode): string
    {
        if (empty($widgetCode['iframe'])) {
            return '<div class="give-tgb-error" style="background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0;">' .
                   __('Donation form not available. Please check your organization configuration.', 'give') .
                   '</div>';
        }

        return '<div class="give-tgb-donation-form" style="text-align: center; margin: 20px 0;">' .
               $widgetCode['iframe'] .
               '</div>';
    }

    /**
     * @unreleased
     *
     * @param array<string, string> $widgetCode
     * @param array<string, string> $atts Shortcode attributes (popup_button_text, popup_button_notice_*).
     */
    private function renderModalButton(array $widgetCode, array $atts): string
    {
        if (empty($widgetCode['popup'])) {
            return '<div class="give-tgb-error" style="background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0;">' .
                   __('Modal button not available. Please check your organization configuration.', 'give') .
                   '</div>';
        }

        $customButtonText = $atts['popup_button_text'] ?? '';
        $wrapperAttrs = 'class="give-tgb-donation-form" style="text-align: center; margin: 20px 0;"';

        $html = '<div ' . $wrapperAttrs . '>' . $widgetCode['popup'];

        if ($customButtonText !== '') {
            $html = str_replace('>Donate Now</button>', '>' . esc_html($customButtonText) . '</button>', $html);
        }

        $noticeEnabled = wp_validate_boolean($atts['popup_button_notice_enable']);
        if ($noticeEnabled) {
            $html .= $this->renderPopupNotice($atts);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Renders the info notice and modal below the popup button.
     *
     * @unreleased
     *
     * @param array<string, string> $atts Shortcode attributes (popup_button_notice_*).
     */
    private function renderPopupNotice(array $atts): string
    {
        $shortText = $atts['popup_button_notice_short_text'] ?? '';
        $longText = $atts['popup_button_notice_long_text'] ?? '';
        $cta = $atts['popup_button_notice_short_cta'] ?? '';

        if ($shortText === '') {
            $shortText = __('Do not affect stats', 'give');
        }
        if ($longText === '') {
            $longText = __(
                'Crypto and stock donations are processed and counted independently from regular donations. Campaign statistics (amount raised, top donors, recent donations, etc.) only include donations made through the standard donation form. They do not include crypto or stock donations made via The Giving Block.',
                'give'
            );
        }
        if ($cta === '') {
            $cta = __('Learn more', 'give');
        }

        $id = 'give-tgb-notice-' . wp_unique_id();

        return '<div class="give-tgb-popup-notice-container" id="' . esc_attr($id) . '">' .
            '<div class="give-tgb-popup-notice">' .
            '<span class="give-tgb-popup-notice-icon" aria-hidden="true"></span>' .
            '<span class="give-tgb-popup-notice-text">' . esc_html($shortText) . '</span> ' .
            '<a href="#" class="give-tgb-notice-cta" role="button" aria-expanded="false" aria-controls="' . esc_attr($id) . '-modal">' . esc_html($cta) . '</a>' .
            '</div>' .
            '<div id="' . esc_attr($id) . '-modal" class="give-tgb-notice-modal" role="dialog" aria-modal="true" aria-label="' . esc_attr__('Information', 'give') . '" hidden>' .
            '<div class="give-tgb-notice-modal-overlay"></div>' .
            '<div class="give-tgb-notice-modal-content">' .
            '<div class="give-tgb-notice-modal-body">' . wp_kses_post(wpautop($longText)) . '</div>' .
            '<button type="button" class="give-tgb-notice-modal-close" aria-label="' . esc_attr__('Close', 'give') . '">' . esc_html__('Close', 'give') . '</button>' .
            '</div>' .
            '</div>' .
            '</div>';
    }
}
