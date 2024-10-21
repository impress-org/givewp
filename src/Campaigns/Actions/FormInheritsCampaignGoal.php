<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 *
 * Form inherits campaign goal
 *
 * @event givewp_donation_form_creating
 */
class FormInheritsCampaignGoal
{
    /**
     * @unreleased
     */
    public function __invoke(DonationForm $donationForm): void
    {
        if (isset($_GET['campaignId'])) {
            $campaign = Campaign::find((int)$_GET['campaignId']);

            if ($campaign) {
                $donationForm->settings->enableDonationGoal = true;
                $donationForm->settings->goalAmount = $campaign->goal;
                $donationForm->settings->goalType = $campaign->goalType->getValue();
            }
        }
    }
}
