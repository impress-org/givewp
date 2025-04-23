<?php

namespace Give\Campaigns\Blocks\CampaignForm;

/**
 * @unreleased
 */
class CampaignFormShortcode
{
    /**
     * @unreleased
     *
     * @param array $atts
     *
     * @return string
     */
    public function renderShortcode($atts): string
    {
        $this->loadAssets();
        $attributes = $this->parseAttributes($atts);

        $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/CampaignForm/render.php';

        ob_start();
        include $renderFile;
        return ob_get_clean();
    }

    /**
     * @unreleased
     */
    public function loadAssets()
    {
        wp_enqueue_script(
            'givewp-campaign-form-app',
            GIVE_PLUGIN_URL . 'build/campaignFormApp.js',
            [],
            null,
            true
        );

        wp_enqueue_style(
            'givewp-campaign-form-style',
            GIVE_PLUGIN_URL . 'build/campaignFormApp.css',
            [],
            null
        );

        wp_enqueue_style('givewp-design-system-foundation');
    }

    /**
     * @unreleased
     */
    private function parseAttributes($atts): array
    {
        $atts = shortcode_atts([
            'campaign_id'        => 0,
            'block_id'           => '',
            'prev_id'            => 0,
            'id'                => 0,
            'display_style'      => 'onpage',
            'continue_button_title' => __('Donate Now', 'give'),
            'show_title'         => true,
            'content_display'    => 'above',
            'show_goal'          => true,
            'show_content'       => true,
        ], $atts, 'givewp_campaign_form');

        return [
            'campaignId'           => (int) $atts['campaign_id'],
            'blockId'              => (string) $atts['block_id'],
            'prevId'               => (int) $atts['prev_id'],
            'id'                    => (int) $atts['id'],
            'displayStyle'         => $atts['display_style'],
            'continueButtonTitle'  => sanitize_text_field($atts['continue_button_title']),
            'showTitle'            => filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN),
            'contentDisplay'       => $atts['content_display'],
            'showGoal'             => filter_var($atts['show_goal'], FILTER_VALIDATE_BOOLEAN),
            'showContent'          => filter_var($atts['show_content'], FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
