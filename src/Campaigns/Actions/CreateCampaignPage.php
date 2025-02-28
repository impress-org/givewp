<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;

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
        $campaignPage = CampaignPage::create([
            'campaignId' => $campaign->id,
        ]);

        $campaign->pageId = $campaignPage->id;
        $campaign->save();
    }
}
