<?php

namespace Give\Campaigns\Actions;

/**
 * @unreleased
 */
class PreventDeletingCampaignPage
{
    public function __invoke($caps, $cap, $userId, $args)
    {
        if ('delete_post' !== $cap || empty($args[0])) {
            return $caps;
        }

        if (get_post_meta($args[0], 'campaignId', true)) {
            $caps[] = 'do_not_allow';
        }

        return $caps;
    }
}
