<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;

/**
 * @since 4.8.0 Remove BlockRenderController dependency
 * @since 4.0.0
 */
class RenderDonateButton
{
    /**
     * @since 4.8.0 Replace BlockRenderController::render with give_form_shortcode
     * @since 4.0.0
     */
    public function __invoke(Campaign $campaign, array $attributes, string $buttonText): string
    {
        $isEditor = defined('REST_REQUEST') && REST_REQUEST;

        ob_start();

        if ($isEditor) {
            echo sprintf(
                '<button type="button" class="givewp-donation-form-modal__open">%s</button>',
                esc_html($buttonText)
            );
        } else {
            echo give_form_shortcode([
                'id' => $campaign->defaultFormId,
                'campaign_id' => $campaign->id,
                'display_style' => 'modal',
                'continue_button_title' => $buttonText,
                'use_default_form' => true,
                'button_color' => $campaign->primaryColor,
                'block_id' => $attributes['blockId'] ?? '',
            ]);
        }

        return (string) ob_get_clean();
    }
}
