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
        $args = [
            'label'           => __('Campaign Page', 'give-peer-to-peer'),
            'labels'          => [],
            'supports'        => [
                'editor',
            ],
            'show_in_rest'    => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'public'          => true,
            'has_archive'     => false,
            'hierarchical'    => false,
            'capability_type' => 'post',
            'rewrite'         => ['slug' => 'campaign'],
            'template'        => [],
        ];

        register_post_type( 'give_campaign_page', $args );
    }
}
