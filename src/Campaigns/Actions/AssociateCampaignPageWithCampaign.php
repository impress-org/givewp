<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;

class AssociateCampaignPageWithCampaign
{
    /**
     * @throws Exception
     */
    public function __invoke(CampaignPage $campaignPage){
        $campaign = Campaign::find($campaignPage->campaignId);
        $campaign->pageId = $campaignPage->id;
        $campaign->save();
    }
}
