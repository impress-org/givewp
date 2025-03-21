<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;

/**
 * @since 4.0.0
 *
 * Deletes campaign page when the campaign is deleted
 *
 * @event givewp_campaign_deleted
 */
class DeleteCampaignPage
{
    /**
     * @since 4.0.0
     */
    public function __invoke(Campaign $campaign): void
    {
        // todo: delete the campaign page
    }
}
