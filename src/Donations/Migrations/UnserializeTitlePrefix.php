<?php

namespace Give\DonationForms\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Helpers\Utils;

/**
 * @unreleased
 */
class UnserializeTitlePrefix extends Migration
{
    /**
     * @unreleased
     */
    public function run()
    {
        $donationMetaTable = DB::prefix('give_donationmeta');
        $sql = DB::prepare("SELECT * FROM $donationMetaTable as fm WHERE fm.meta_key = '_give_donor_billing_title_prefix'");
        $items = DB::get_results($sql);

        foreach ($items as $item) {
            if (Utils::isSerialized($item->meta_value)) {
                $unserializedTitlePrefix = Utils::safeUnserialize($item->meta_value);

                DB::table('give_donationmeta')
                    ->where('donation_id', $item->donation_id)
                    ->where('meta_key', '_give_donor_billing_title_prefix')
                    ->update([
                        'meta_value' => $unserializedTitlePrefix,
                    ]);
            }
        }
    }


    /**
     * @inheritdoc
     */
    public static function id()
    {
        return 'donation-meta-unserialize-title-prefix';
    }

    /**
     * @inheritdoc
     */
    public static function title()
    {
        return 'Unserialize data in the _give_donor_billing_title_prefix meta value';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2024-23-10');
    }
}
