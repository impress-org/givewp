<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Views\View;

/**
 * @since 4.0.0
 */
class CampaignPageTemplate
{
    /**
     * @since 4.0.0
     */
    public function registerTemplate()
    {
        if (!$this->canRegisterBlockTemplate()) {
            return;
        }

        register_block_template('givewp//campaign-page-template', [
            'title' => __('Campaign page', 'give'),
            'description' => __('Give Campaign Page template', 'give'),
            'post_types' => [
                'give_campaign_page',
            ],
            'content' => View::load('Campaigns.campaign-page-content'),
        ]);
    }

    /**
     * @since 4.0.0
     */
    public function loadTemplate($template)
    {
        if (
            'give_campaign_page' === get_query_var('post_type')
            && current_theme_supports('block-templates')
        ) {
            if (!$this->isPageVisible()) {
                status_header(404);

                return get_404_template();
            }

            $template = $this->canRegisterBlockTemplate()
                ? $template
                : GIVE_PLUGIN_DIR . '/src/Campaigns/resources/views/campaign-page-template.php';

            return locate_block_template($template, 'campaign-page-template', ['campaign-page-template.php']);
        }

        return $template;
    }

    /**
     * @since 4.0.0
     */
    private function canRegisterBlockTemplate(): bool
    {
        return function_exists('register_block_template');
    }

    /**
     *
     * @since 4.0.0
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
