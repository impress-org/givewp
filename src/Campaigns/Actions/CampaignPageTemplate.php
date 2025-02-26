<?php

namespace Give\Campaigns\Actions;

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
        register_block_template('givewp//campaign-page', [
            'title' => __('Campaign page', 'give'),
            'description' => __('Give Campaign Page template', 'give'),
            'post_types' => 'give_campaign_page',
            'content' => View::load('Campaigns.campaign-page'),
        ]);
    }

    /**
     * @unreleased
     */
    public function loadTemplate($template)
    {
        if (
            'give_campaign_page' === get_query_var('post_type')
            && current_theme_supports('block-templates')
        ) {
            return locate_block_template($template, 'campaign-page', []);
        }

        return $template;
    }
}
