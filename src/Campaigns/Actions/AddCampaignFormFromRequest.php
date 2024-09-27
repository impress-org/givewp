<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Exceptions\Primitives\Exception;

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
            $campaign->saveWithForm($donationForm);
        }
    }
}
