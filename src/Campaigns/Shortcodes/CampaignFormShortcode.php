<?php

namespace Give\Campaigns\Shortcodes;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.3.0
 */
class CampaignFormShortcode
{
    /**
     * @since 4.7.0 updated to use ShortcodeRenderController
     * @since 4.3.0
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

        return ShortcodeRenderController::renderWithBlockContext(
            $renderFile,
            'givewp/campaign-form-block',
            $attributes
        );
    }

    /**
     * @since 4.3.0
     */
    public function loadAssets()
    {
        $handleName = 'givewp-campaign-form-app';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignFormBlockApp.asset.php');

        wp_enqueue_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignFormBlockApp.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignFormBlockApp.css',
            [],
            $asset['version']
        );

        wp_enqueue_style('givewp-design-system-foundation');
    }

    /**
     * @since 4.3.0
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
            'use_default_form'   => true,
        ], $atts, 'givewp_campaign_form');

        return [
            'campaignId'           => (int) $atts['campaign_id'],
            'blockId'              => (string) $atts['block_id'],
            'prevId'               => (int) $atts['prev_id'],
            'id'                   => (int) $atts['id'],
            'displayStyle'         => $atts['display_style'],
            'continueButtonTitle'  => sanitize_text_field($atts['continue_button_title']),
            'showTitle'            => filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN),
            'contentDisplay'       => $atts['content_display'],
            'showGoal'             => filter_var($atts['show_goal'], FILTER_VALIDATE_BOOLEAN),
            'showContent'          => filter_var($atts['show_content'], FILTER_VALIDATE_BOOLEAN),
            'useDefaultForm'       => filter_var($atts['use_default_form'], FILTER_VALIDATE_BOOLEAN),
        ];
    }
}
