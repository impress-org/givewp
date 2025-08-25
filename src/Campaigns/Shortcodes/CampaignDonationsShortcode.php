<?php

namespace Give\Campaigns\Shortcodes;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.5.0
 */
class CampaignDonationsShortcode
{
    /**
     * @since 4.7.0 updated to use ShortcodeRenderController
     * @since 4.5.0
     */
    public function renderShortcode($atts): string
    {
        $this->loadAssets();
        $attributes = $this->parseAttributes($atts);

        $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/CampaignDonations/render.php';

        return ShortcodeRenderController::renderWithBlockContext(
            $renderFile,
            'givewp/campaign-donations-block',
            $attributes
        );
    }

    /**
     * @since 4.5.0
     */
    public function loadAssets()
    {
        $handleName = 'givewp-campaign-donations-block-app';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignDonationsBlockApp.asset.php');

        wp_enqueue_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignDonationsBlockApp.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignDonationsBlockApp.css',
            [],
            $asset['version']
        );

        wp_enqueue_style('givewp-design-system-foundation');
    }

    /**
     * @since 4.5.0
     */
    private function parseAttributes($atts): array
    {
        $atts = shortcode_atts([
            'campaign_id'           => 0,
            'show_anonymous'        => true,
            'show_icon'             => true,
            'show_button'           => true,
            'donate_button_text'    => __('Donate', 'give'),
            'sort_by'               => 'recent-donations',
            'donations_per_page'    => 5,
            'load_more_button_text' => __('Load more', 'give'),
        ], $atts, 'givewp_campaign_donations');

        return [
            'campaignId'         => (int) $atts['campaign_id'],
            'showAnonymous'      => filter_var($atts['show_anonymous'], FILTER_VALIDATE_BOOLEAN),
            'showIcon'           => filter_var($atts['show_icon'], FILTER_VALIDATE_BOOLEAN),
            'showButton'         => filter_var($atts['show_button'], FILTER_VALIDATE_BOOLEAN),
            'donateButtonText'   => (string) $atts['donate_button_text'],
            'sortBy'             => (string) $atts['sort_by'],
            'donationsPerPage'   => (int) $atts['donations_per_page'],
            'loadMoreButtonText' => (string) $atts['load_more_button_text'],
        ];
    }
}
