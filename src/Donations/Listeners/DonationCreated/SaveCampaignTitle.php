<?php

namespace Give\Donations\Listeners\DonationCreated;

use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;

/**
 * Class SaveCampaignTitle
 *
 * @since @unreleased
 */
class SaveCampaignTitle
{
    /**
     * @since @unreleased
     *
     * @param Donation $donation
     */
    public function __invoke(Donation $donation)
    {
        if (!$donation->campaignId) {
            return;
        }

        // Check if campaign title is already saved
        $existingTitle = DB::table('give_donationmeta')
            ->select('meta_value')
            ->where('donation_id', $donation->id)
            ->where('meta_key', DonationMetaKeys::CAMPAIGN_TITLE)
            ->get();

        if ($existingTitle) {
            return;
        }

        // Get campaign title
        $campaign = give()->campaigns->getById($donation->campaignId);
        
        if (!$campaign) {
            return;
        }

        // Save campaign title to donation meta
        DB::table('give_donationmeta')->insert([
            'donation_id' => $donation->id,
            'meta_key' => DonationMetaKeys::CAMPAIGN_TITLE,
            'meta_value' => $campaign->title,
        ]);
    }
}
