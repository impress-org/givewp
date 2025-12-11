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
        }
    }
}
