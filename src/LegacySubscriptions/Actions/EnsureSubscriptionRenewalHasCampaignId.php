<?php

namespace Give\LegacySubscriptions\Actions;

use Give\Donations\Models\Donation;
use Give_Payment;
use Give_Subscription;

/**
 * Class EnsureSubscriptionRenewalHasCampaignId
 *
 * This action ensures that legacy subscription renewal payments inherit the campaignId
 * from their parent payment, maintaining backwards compatibility for campaign tracking.
 *
 * @unreleased
 */
class EnsureSubscriptionRenewalHasCampaignId
{
    /**
     * Adds campaignId to subscription renewal payments when they don't have one.
     *
     * @unreleased
     *
     * @param Give_Payment $payment The renewal payment
     * @param Give_Subscription $subscription The subscription
     * @return void
     */
    public function __invoke(Give_Payment $payment, Give_Subscription $subscription): void
    {
        // Get the renewal donation using the Donation model
        $renewalDonation = Donation::find($payment->ID);

        if (!$renewalDonation) {
            return; // Unable to find the donation
        }

        // Check if the renewal donation already has a campaignId
        if (!empty($renewalDonation->campaignId)) {
            return; // Already has a campaignId, nothing to do
        }

        // Get the parent donation using the Donation model
        $parentDonation = Donation::find($subscription->parent_payment_id);

        if (!$parentDonation) {
            return; // Parent donation not found
        }

        $campaignId = null;

        // First, try to get the campaignId from the parent donation
        if (!empty($parentDonation->campaignId)) {
            $campaignId = $parentDonation->campaignId;
        } else {
            // If the parent donation doesn't have a campaignId, try to find the campaign by form ID
            $campaign = give()->campaigns->getByFormId($parentDonation->formId);
            if ($campaign) {
                $campaignId = $campaign->id;
            }
        }

        if ($campaignId) {
            // Set the campaignId on the renewal donation and save
            $renewalDonation->campaignId = $campaignId;
            $renewalDonation->save();
        }
    }
}
