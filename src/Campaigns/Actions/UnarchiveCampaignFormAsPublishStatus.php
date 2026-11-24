<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Database\DB;

/**
 * @since 4.2.0
 */
class UnarchiveCampaignFormAsPublishStatus
{
    /**
     * @since TBD Clear the post cache of the default form so persistent object caches do not serve the old status.
     * @since 4.2.0
     */
    public function __invoke(Campaign $campaign)
    {
        if ($campaign->isDirty('status') &&
            $campaign->status->isActive() &&
            $campaign->getOriginal('status')->isArchived()) {
            if (!$campaign->defaultFormId) {
                return;
            }

            DB::table('posts')
                ->where('ID', $campaign->defaultFormId)
                ->update([
                    'post_status' => 'publish',
                ]);

            clean_post_cache($campaign->defaultFormId);
        }
    }
}
