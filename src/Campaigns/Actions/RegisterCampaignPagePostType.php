<?php

namespace Give\Campaigns\Actions;

/**
 * @unreleased
 */
class RegisterCampaignPagePostType
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        register_post_type( 'give_campaign_page', [
            'label' => __('Campaign Page', 'give-peer-to-peer'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => true,
            'supports' => [
                'editor'
            ],
            'rewrite' => [
                'slug' => 'campaign'
            ],
            'template' => [
                // TODO: Add default blocks template.
            ],
        ] );
    }
}
