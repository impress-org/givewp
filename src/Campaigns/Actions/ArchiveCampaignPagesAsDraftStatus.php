<?php

namespace Give\Campaigns\Actions;


use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\Repositories\CampaignPageRepository;
use Give\Campaigns\ValueObjects\CampaignPageStatus;

/**
 * When a Campaign is archived, set all pages to Draft Status
 *
 * @since 4.0.0
 */
class ArchiveCampaignPagesAsDraftStatus
{
    /**
     * @since 4.0.0
     */
    public function __invoke(Campaign $campaign)
    {
        if (!$campaign->status->isArchived()) {
            return;
        }

        /** @var CampaignPage[]|null $pages */
        $pages = give(CampaignPageRepository::class)->queryByCampaignId($campaign->id)->getAll();

        if (!$pages) {
            return;
        }

        foreach ($pages as $page) {
            if ($page->status->isPublish()) {
                $page->status = CampaignPageStatus::DRAFT();
                $page->save();
            }
        }
    }
}
