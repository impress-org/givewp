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
        if (isset($_GET['campaignId'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- campaign id only picks which campaign goal the new form inherits; it is cast to int before use.
            $campaign = Campaign::find((int)$_GET['campaignId']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- campaign id only picks which campaign goal the new form inherits; it is cast to int before use.

            if ($campaign) {
                $donationForm->settings->goalSource = GoalSource::CAMPAIGN();
            }
        }
    }
}
