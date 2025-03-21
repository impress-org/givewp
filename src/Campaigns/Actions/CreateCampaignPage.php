<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\ValueObjects\CampaignPageStatus;

/**
 * @unreleased
 */
class CreateCampaignPage
{
    /**
     * @throws Exception
     */
    public function __invoke(Campaign $campaign)
    {
        if ( ! $campaign->type->isCore()) {
            return;
        }

        $campaignPage = CampaignPage::create([
            'campaignId' => $campaign->id,
            'status' => CampaignPageStatus::PUBLISH(),
        ]);

        $campaign->pageId = $campaignPage->id;
        $campaign->save();
    }
}
