<?php

namespace Give\Campaigns\Actions;

/**
 * @unreleased
 */
class RegisterCampaignIdRestField
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        //TODO: update this to be more GiveWP specific like give_campaign_id
        register_rest_field(
            'post',
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
