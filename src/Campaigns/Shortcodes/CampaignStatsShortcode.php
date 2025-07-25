<?php

namespace Give\Campaigns\Shortcodes;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @unreleased
 */
class CampaignStatsShortcode
{
    /**
     * @unreleased
     *
     * @param array<string, mixed> $atts
     *
     * @return string
     */
    public function renderShortcode($atts): string
    {
        $this->loadAssets();
        $attributes = $this->parseAttributes($atts);

        $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/CampaignStats/render.php';

        ob_start();
        include $renderFile;
        return ob_get_clean();
    }

    /**
     * @since 4.3.0
     */
    public function loadAssets(): void
    {
        $handleName = 'givewp-campaign-stats-block-app';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignStatsBlockApp.asset.php');

        wp_enqueue_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignStatsBlockApp.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignStatsBlockApp.css',
            [],
            $asset['version']
        );

        wp_enqueue_style('givewp-design-system-foundation');
    }

    /**
     * @unreleased
     */
    private function parseAttributes($atts): array
    {
        $atts = shortcode_atts([
            'campaign_id' => 0,
            'statistic'   => 'top-donation',
        ], $atts, 'givewp_campaign_stats');

        return [
            'campaignId' => (int) $atts['campaign_id'],
            'statistic'  => $atts['statistic'],
        ];
    }
}
