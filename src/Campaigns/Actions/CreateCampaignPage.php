<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\ValueObjects\CampaignPageStatus;

/**
 * @since 4.0.0
 */
class CreateCampaignPage
{
    /**
     * @throws Exception
     */
    public function __invoke(Campaign $campaign)
    {
        if (
            $campaign->pageId
            || ! $campaign->type->isCore()
        ) {
            return;
        }

        $campaignPage = CampaignPage::create([
            'campaignId' => $campaign->id,
            'status' => CampaignPageStatus::DRAFT(),
        ]);

        $campaign->pageId = $campaignPage->id;
        $campaign->save();
    }
}
