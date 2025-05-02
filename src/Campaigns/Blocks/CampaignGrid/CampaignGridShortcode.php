<?php

namespace Give\Campaigns\Blocks\CampaignGrid;

/**
 * @since 4.2.0
 */
class CampaignGridShortcode
{
    /**
     * @since 4.2.0
     *
     * @param array $atts
     *
     * @return string
     */
    public function renderShortcode($atts): string
    {
        $this->loadAssets();
        $attributes = $this->parseAttributes($atts);

        $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/CampaignGrid/render.php';

        ob_start();
        include $renderFile;
        return ob_get_clean();
    }

    /**
     * @since 4.2.0
     */
    public function loadAssets()
    {
        wp_enqueue_script(
            'givewp-campaign-grid-app',
            GIVE_PLUGIN_URL . 'build/campaignGridApp.js',
            [],
            null,
            true
        );

        wp_enqueue_style(
            'givewp-campaign-grid-style',
            GIVE_PLUGIN_URL . 'build/campaignGridApp.css',
            [],
            null
        );

        wp_enqueue_style('givewp-design-system-foundation');
    }

    /**
     * @since 4.2.0
     */
    private function parseAttributes($atts): array
    {
        $atts = shortcode_atts([
            'layout'           => 'full',
            'show_image'       => true,
            'show_description' => true,
            'show_goal'        => true,
            'sort_by'          => 'date',
            'order_by'         => 'desc',
            'per_page'         => 6,
            'show_pagination'  => true,
            'filter_by'        => null,
        ], $atts, 'givewp_campaign_grid');

        return [
            'layout'          => $atts['layout'],
            'showImage'       => filter_var($atts['show_image'], FILTER_VALIDATE_BOOLEAN),
            'showDescription' => filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN),
            'showGoal'        => filter_var($atts['show_goal'], FILTER_VALIDATE_BOOLEAN),
            'sortBy'          => $atts['sort_by'],
            'orderBy'         => $atts['order_by'],
            'filterBy'        => $atts['filter_by'],
            'perPage'         => (int)$atts['per_page'],
            'showPagination'  => filter_var($atts['show_pagination'], FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
