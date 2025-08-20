<?php

namespace Give\Campaigns\Shortcodes;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.5.0
 */
class CampaignCommentsShortcode
{
    /**
     * @since 4.7.0 updated to use ShortcodeRenderController
     * @since 4.5.0
     */
    public function renderShortcode($atts): string
    {
        $this->loadAssets();
        $attributes = $this->parseAttributes($atts);

        $renderFile = GIVE_PLUGIN_DIR . 'src/Campaigns/Blocks/CampaignComments/render.php';

        return ShortcodeRenderController::renderWithBlockContext(
            $renderFile,
            'givewp/campaign-comments-block',
            $attributes
        );
    }

    /**
     * @since 4.5.0
     */
    public function loadAssets()
    {
        $handleName = 'givewp-campaign-comments-block-app';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignCommentsBlockApp.asset.php');

        wp_enqueue_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignCommentsBlockApp.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignCommentsBlockApp.css',
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
            'block_id'         => '',
            'campaign_id'      => 0,
            'title'            => '',
            'show_anonymous'   => true,
            'show_avatar'      => true,
            'show_date'        => true,
            'show_name'        => true,
            'comment_length'   => 200,
            'read_more_text'   => '',
            'comments_per_page'=> 3,
        ], $atts, 'givewp_campaign_comments');

        return [
            'blockId'         => (string) $atts['block_id'],
            'campaignId'      => (int) $atts['campaign_id'],
            'title'           => (string) $atts['title'],
            'showAnonymous'   => filter_var($atts['show_anonymous'], FILTER_VALIDATE_BOOLEAN),
            'showAvatar'      => filter_var($atts['show_avatar'], FILTER_VALIDATE_BOOLEAN),
            'showDate'        => filter_var($atts['show_date'], FILTER_VALIDATE_BOOLEAN),
            'showName'        => filter_var($atts['show_name'], FILTER_VALIDATE_BOOLEAN),
            'commentLength'   => (int) $atts['comment_length'],
            'readMoreText'    => (string) $atts['read_more_text'],
            'commentsPerPage' => (int) $atts['comments_per_page'],
        ];
    }
}
