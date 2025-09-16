<?php

namespace Give\Donations\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class AddCampaignTitleToDonations
 *
 * @since @unreleased
 */
class AddCampaignTitleToDonations extends Migration
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        $donationMetaTable = DB::prefix('give_donationmeta');
        $campaignsTable = DB::prefix('give_campaigns');
        $postsTable = DB::prefix('posts');

        // Update donations that have a campaign_id but no campaign_title
        DB::query(
            "
            INSERT INTO $donationMetaTable (donation_id, meta_key, meta_value)
            SELECT 
                dm_campaign.donation_id,
                '_give_payment_campaign_title',
                campaigns.campaign_title
            FROM $donationMetaTable dm_campaign
            INNER JOIN $campaignsTable campaigns ON campaigns.id = dm_campaign.meta_value
            LEFT JOIN $donationMetaTable dm_title ON dm_title.donation_id = dm_campaign.donation_id 
                AND dm_title.meta_key = '_give_payment_campaign_title'
            WHERE dm_campaign.meta_key = '_give_campaign_id'
                AND dm_title.meta_value IS NULL
                AND dm_campaign.meta_value IS NOT NULL
                AND dm_campaign.meta_value != '0'
            "
        );
    }

    /**
     * @inheritdoc
     */
    public static function id()
    {
        return 'add-campaign-title-to-donations';
    }

    /**
     * @inheritdoc
     */
    public static function title()
    {
        return 'Add campaign title to donations';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2024-12-19');
    }
}
