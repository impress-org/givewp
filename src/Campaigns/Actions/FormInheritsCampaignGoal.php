<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\GoalSource;

/**
 * @since 4.0.0
 *
 * Form inherits campaign goal
 *
 * @event givewp_donation_form_creating
 */
class FormInheritsCampaignGoal
{
    /**
     * @since 4.0.0
     */
    public function __invoke(DonationForm $donationForm): void
    {
        if (isset($_GET['campaignId'])) {
            $campaign = Campaign::find((int)$_GET['campaignId']);

            if ($campaign) {
                $donationForm->settings->goalSource = GoalSource::CAMPAIGN();
            }
        }
    }
}
