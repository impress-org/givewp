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
        if ( ! $this->canWeRegisterBlockTemplate()) {
            return;
        }

        register_block_template('givewp//campaign-page-template', [
            'title' => __('Campaign page', 'give'),
            'description' => __('Give Campaign Page template', 'give'),
            'post_types' => 'give_campaign_page',
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
            && current_theme_supports('block-templates')
        ) {
            $template = $this->canWeRegisterBlockTemplate()
                ? $template
                : GIVE_PLUGIN_DIR . '/src/Campaigns/resources/views/campaign-page-template.php';

            return locate_block_template($template, 'campaign-page-template', ['campaign-page-template.php']);
        }

        return $template;
    }

    /**
     * @unreleased
     */
    private function canWeRegisterBlockTemplate(): bool
    {
        return function_exists('register_block_template');
    }
}
