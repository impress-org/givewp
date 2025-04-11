<?php

namespace Give\Campaigns\Blocks\Campaign;

/**
 * @unreleased
 */
class CampaignShortcode
{

    /**
     * @unreleased
     */
    public function __invoke()
    {
        add_shortcode('givewp_campaign', [$this, 'renderShortcode']);
    }

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

        $render_file = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/Campaign/render.php';

        if (file_exists($render_file)) {
            ob_start();
            include $render_file;

            return ob_get_clean();
        }

        return '';
    }

    /**
     * @unreleased
     */
    public function loadAssets()
    {
        wp_enqueue_script(
            'givewp-campaign-block-app',
            GIVE_PLUGIN_URL . 'build/campaignBlockApp.js',
            [],
            null,
            true
        );

        wp_enqueue_style(
            'givewp-campaign-block-style',
            GIVE_PLUGIN_URL . 'build/campaignBlockApp.css',
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
            'campaign_id'      => '',
            'show_image'       => true,
            'show_description' => true,
            'show_goal'        => true,
        ], $atts, 'givewp_campaign');

        return [
            'campaignId'      => $atts['campaign_id'],
            'showImage'       => filter_var($atts['show_image'], FILTER_VALIDATE_BOOLEAN),
            'showDescription' => filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN),
            'showGoal'        => filter_var($atts['show_goal'], FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
