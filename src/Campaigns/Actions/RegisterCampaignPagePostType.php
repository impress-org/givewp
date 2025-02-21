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
        register_post_type('give_campaign_page', [
            'label' => __('Campaign Page', 'give'),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => true,
            'show_in_admin_bar' => true,
            'supports' => [
                'title',
                'editor',
            ],
            'rewrite' => [
                'slug' => 'campaign',
                'with_front' => true,
            ],
            'template' => [],
        ]);
    }
}
