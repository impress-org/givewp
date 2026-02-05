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
            return $this->renderModalButton($widgetCode, $atts['popup_button_text']);
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
     * @param string                $customButtonText Optional CTA text to replace default "Donate Now".
     */
    private function renderModalButton(array $widgetCode, string $customButtonText = ''): string
    {
        if (empty($widgetCode['popup'])) {
            return '<div class="give-tgb-error" style="background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0;">' .
                   __('Modal button not available. Please check your organization configuration.', 'give') .
                   '</div>';
        }

        $wrapperAttrs = 'class="give-tgb-donation-form" style="text-align: center; margin: 20px 0;"';
        if ($customButtonText !== '') {
            $wrapperAttrs .= ' data-tgb-button-text="' . esc_attr($customButtonText) . '"';
        }

        $html = '<div ' . $wrapperAttrs . '>' . $widgetCode['popup'] . '</div>';

        if ($customButtonText !== '') {
            $html = str_replace('>Donate Now</button>', '>' . esc_html($customButtonText) . '</button>', $html);
        }

        return $html;
    }
}
