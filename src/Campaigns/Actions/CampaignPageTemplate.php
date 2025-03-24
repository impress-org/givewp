<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Views\View;

/**
 * @unreleased
 */
class CampaignPageTemplate
{
    /**
     * @unreleased
     */
    public function registerTemplate()
    {
        if (!$this->canRegisterBlockTemplate()) {
            return;
        }

        register_block_template('givewp//single-give_campaign_page', [
            'title' => __('Campaign page', 'give'),
            'description' => __('Give Campaign Page template', 'give'),
            'post_types' => [
                'give_campaign_page',
            ],
            'content' => View::load('Campaigns.campaign-page-content'),
        ]);
    }

    /**
     * @unreleased
     */
    public function loadTemplate($template)
    {
        if (
            'give_campaign_page' === get_query_var('post_type')
            && ( $this->isFSETheme() || current_theme_supports( 'block-template-parts' ) )
        ) {
            if (!$this->isPageVisible()) {
                status_header(404);

                return get_404_template();
            }

            $template = $this->canRegisterBlockTemplate()
                ? $template
                : GIVE_PLUGIN_DIR . '/src/Campaigns/resources/views/campaign-page-template.php';

            return locate_block_template($template, 'campaign-page-template', ['campaign-page-template.php']);
        } else if ('give_campaign_page' === get_query_var('post_type') && strpos($template, 'canvas.php') !== false ) {
            // If the theme is not an FSE theme we should not load the canvas template.
            $template = get_template_directory() . '/single-give_campaign_page.php';
            if ( ! file_exists( $template ) ) {
                $template = get_template_directory() . '/single.php';
            }
            if ( ! file_exists( $template ) ) {
                $template = get_template_directory() . '/index.php';
            }
        }

        return $template;
    }

    /**
     * @unreleased
     */
    private function canRegisterBlockTemplate(): bool
    {
        return function_exists('register_block_template');
    }

    /**
     * Check if the current theme is a block theme.
     *
     * @since 6.0.0
     * @return bool
     */
    private function isFSETheme() {
        if ( function_exists( 'wp_is_block_theme' ) ) {
            return (bool) wp_is_block_theme();
        }
        if ( function_exists( 'gutenberg_is_fse_theme' ) ) {
            return (bool) gutenberg_is_fse_theme();
        }

        return false;
    }

    /**
     *
     * @unreleased
     */
    private function isPageVisible(): bool
    {
        $campaignId = get_post_field('campaignId');

        if (!$campaignId) {
            return false;
        }

        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            return false;
        }

        // if the campaign page is disabled, no one can see it
        if (!$campaign->enableCampaignPage) {
            return false;
        }

        // logged-in users can see the page if the campaign is active or draft
        if (current_user_can('manage_options') && ($campaign->status->isActive() || $campaign->status->isDraft()) ) {
            return true;
        }

        return $campaign->status->isActive();
    }
}
