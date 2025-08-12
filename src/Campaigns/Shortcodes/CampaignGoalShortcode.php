<?php

namespace Give\Campaigns\Shortcodes;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @unreleased
 */
class CampaignGoalShortcode
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

        $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/CampaignGoal/render.php';

        return ShortcodeRenderController::renderWithBlockContext(
            $renderFile,
            'givewp/campaign-goal-block',
            $attributes
        );
    }

    /**
     * @since 4.3.0
     */
    public function loadAssets(): void
    {
        $handleName = 'givewp-campaign-goal-block-app';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignGoalBlockApp.asset.php');

        wp_enqueue_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignGoalBlockApp.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignGoalBlockApp.css',
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
        ], $atts, 'givewp_campaign_goal');

        return [
            'campaignId' => (int) $atts['campaign_id'],
        ];
    }
}
