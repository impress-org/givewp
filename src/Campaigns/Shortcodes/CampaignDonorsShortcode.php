<?php

namespace Give\Campaigns\Shortcodes;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.5.0
 */
class CampaignDonorsShortcode
{
    /**
     * @since 4.7.0 updated to use ShortcodeRenderController
     * @since 4.5.0
     */
    public function renderShortcode($atts): string
    {
        $this->loadAssets();
        $attributes = $this->parseAttributes($atts);

        $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/CampaignDonors/render.php';

        return ShortcodeRenderController::renderWithBlockContext(
            $renderFile,
            'givewp/campaign-donors-block',
            $attributes
        );
    }

    /**
     * @since 4.5.0
     */
    public function loadAssets()
    {
        $handleName = 'givewp-campaign-donors-block-app';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignDonorsBlockApp.asset.php');

        wp_enqueue_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignDonorsBlockApp.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignDonorsBlockApp.css',
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
            'block_id'              => '',
            'show_anonymous'        => true,
            'show_company_name'     => true,
            'show_avatar'           => true,
            'show_button'           => true,
            'donate_button_text'    => __('Join the list', 'give'),
            'sort_by'               => 'top-donors',
            'donors_per_page'       => 5,
            'load_more_button_text' => __('Load more', 'give'),
        ], $atts, 'givewp_campaign_donors');

        return [
            'campaignId'         => (int) $atts['campaign_id'],
            'blockId'            => (string) $atts['block_id'],
            'showAnonymous'      => filter_var($atts['show_anonymous'], FILTER_VALIDATE_BOOLEAN),
            'showCompanyName'    => filter_var($atts['show_company_name'], FILTER_VALIDATE_BOOLEAN),
            'showAvatar'         => filter_var($atts['show_avatar'], FILTER_VALIDATE_BOOLEAN),
            'showButton'         => filter_var($atts['show_button'], FILTER_VALIDATE_BOOLEAN),
            'donateButtonText'   => (string) $atts['donate_button_text'],
            'sortBy'             => (string) $atts['sort_by'],
            'donorsPerPage'      => (int) $atts['donors_per_page'],
            'loadMoreButtonText' => (string) $atts['load_more_button_text'],
        ];
    }
}
