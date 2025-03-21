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

        if ('give_campaign_page' === get_post_type($args[0])) {
            $caps[] = 'do_not_allow';
        }

        return $caps;
    }
}
