<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Donations\ValueObjects\DonationMetaKeys;

/**
 * Resolve a campaign name for gateway metadata and payment descriptions.
 *
 * Prefers the Campaign title when available, falling back to the donation form title.
 *
 * @since TBD
 */
class GetCampaignName
{
    /**
     * @since TBD
     *
     * @param int $donationId Donation ID when available.
     * @param int $formId     Form ID when the donation ID is unknown (e.g. PayPal order creation).
     */
    public function __invoke(int $donationId = 0, int $formId = 0): string
    {
        if (!$formId && $donationId) {
            $formId = give_get_payment_form_id($donationId);
        }

        $campaign = null;

        if ($donationId) {
            $campaignId = absint(give_get_meta($donationId, DonationMetaKeys::CAMPAIGN_ID, true));
            $campaign = $campaignId ? Campaign::find($campaignId) : null;
        }

        if (!$campaign && $formId) {
            $campaign = Campaign::findByFormId($formId);
        }

        if ($campaign && !empty($campaign->title)) {
            return $campaign->title;
        }

        return $formId ? (string) get_the_title($formId) : '';
    }
}
