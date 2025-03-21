<?php

namespace Give\Campaigns\Actions;

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
            'give_campaign_page',
            'campaignId',
            [
                'get_callback' => function ($object) {
                    return get_post_meta($object['id'], 'campaignId', true);
                },
                'update_callback' => function ($value, $object) {
                    return update_post_meta($object->ID, 'campaignId', (int) $value);
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
