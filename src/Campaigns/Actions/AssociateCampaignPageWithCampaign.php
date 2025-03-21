<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;

/**
 * @unreleased
 */
class AssociateCampaignPageWithCampaign
{
    /**
     * @throws Exception
     */
    public function __invoke(CampaignPage $campaignPage){
        $campaign = Campaign::find($campaignPage->campaignId);

        if ($campaign) {
            $campaign->pageId = $campaignPage->id;
            $campaign->save();
        }
    }
}
