<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;

/**
 * @unreleased
 *
 * Deletes campaign page when the campaign is deleted
 *
 * @event givewp_campaign_deleted
 */
class DeleteCampaignPage
{
    /**
     * @unreleased
     */
    public function __invoke(Campaign $campaign): void
    {
        // todo: delete the campaign page
    }
}
