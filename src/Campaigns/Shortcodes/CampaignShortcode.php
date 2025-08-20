<?php

namespace Give\Campaigns\Shortcodes;

use Give\Campaigns\Actions\LoadCampaignPublicOptions;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.2.0
 */
class CampaignShortcode
{
    /**
     * @since 4.7.0 updated to use ShortcodeRenderController
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

        $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/Campaign/render.php';

        return ShortcodeRenderController::renderWithBlockContext(
            $renderFile,
            'givewp/campaign-block',
            $attributes
        );
    }

    /**
     * @since 4.3.0 Use info from asset.php file and set script translations
     * @since 4.2.0
     */
    public function loadAssets()
    {
        give(LoadCampaignPublicOptions::class)();

        $handleName = 'givewp-campaign-block-app';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignBlockApp.asset.php');

        wp_enqueue_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignBlockApp.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignBlockApp.css',
            [],
            $asset['version']
        );

        wp_enqueue_style('givewp-design-system-foundation');
    }

    /**
     * @since 4.2.0
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
