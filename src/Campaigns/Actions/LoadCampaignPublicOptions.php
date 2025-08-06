<?php

namespace Give\Campaigns\Actions;

use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;

/**
 * The purpose of this action is to have a centralized place for localizing options used on frontend
 * by campaign scripts (blocks, etc.)
 *
 * @since 4.6.1
 */
class LoadCampaignPublicOptions
{
    public function __invoke()
    {
        wp_register_script('give-campaign-options', false);

        wp_localize_script('give-campaign-options', 'GiveCampaignOptions',
            [
                'isAdmin' => false,
                'currency' => give_get_currency(),
            ]
        );

        wp_enqueue_script('give-campaign-options');
    }
}
