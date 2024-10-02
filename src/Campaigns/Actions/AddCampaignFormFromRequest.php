<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 */
class AddCampaignFormFromRequest
{
    /**
     * @throws Exception
     */
    public function __invoke(DonationForm $donationForm)
    {
        if (isset($_GET['campaignId']) && $campaign = Campaign::find(absint($_GET['campaignId']))) {
            give(CampaignRepository::class)->addCampaignForm($campaign, $donationForm);
        }
    }
}
