<?php

namespace Give\Campaigns\Actions;


use Give\Campaigns\Models\Campaign;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\JoinQueryBuilder;

/**
 * When a Campaign is archived, set all Forms to Draft Status
 *
 * @since 4.0.0
 */
class ArchiveCampaignFormsAsDraftStatus
{
    /**
     * TODO: update this to use single update query (QB was not working with whereIn and update)
     * @since 4.0.0
     */
    public function __invoke(Campaign $campaign)
    {
        if (!$campaign->status->isArchived()) {
            return;
        }

        $query = DB::table('posts')
            ->where('post_type', 'give_forms')
            ->where('post_status', 'publish')
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin("give_campaign_forms", "campaign_forms")
                    ->on("campaign_forms.form_id", "id");
            })
            ->where("campaign_forms.campaign_id", $campaign->id)
            ->getAll();

        $ids = array_values(array_column($query, 'ID'));

        foreach ($ids as $id) {
            DB::table('posts')
                ->where('ID', $id)
                ->update(
                    ['post_status' => 'draft']
                );
        }
    }
}
