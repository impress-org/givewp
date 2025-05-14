<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;

/**
 * @since 4.0.0
 */
class RegisterCampaignIdRestField
{
    /**
     * @since 4.0.0
     */
    public function __invoke()
    {
        register_rest_field(
            'page',
            'campaignId',
            [
                'get_callback' => function ($object) {
                    return get_post_meta($object['id'], CampaignPageMetaKeys::CAMPAIGN_ID, true);
                },
                'update_callback' => function ($value, $object) {
                    return update_post_meta($object->ID, CampaignPageMetaKeys::CAMPAIGN_ID, (int) $value);
                },
                'schema' => [
                    'description' => 'Campaign ID',
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                ],
            ]
        );
    }
}
