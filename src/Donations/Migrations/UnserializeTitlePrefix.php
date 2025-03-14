<?php

namespace Give\Donations\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Helpers\Utils;

/**
 * @since 3.17.2
 */
class UnserializeTitlePrefix extends Migration
{
    /**
     * @since 3.17.2
     */
    public function run()
    {
        $items = DB::table('give_donationmeta')->where('meta_key', '_give_donor_billing_title_prefix')->getAll();

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
     * @since 3.17.2
     */
    public static function id()
    {
        return 'donation-meta-unserialize-title-prefix';
    }

    /**
     * @since 3.17.2
     */
    public static function title()
    {
        return 'Unserialize data in the _give_donor_billing_title_prefix meta value';
    }

    /**
     * @since 3.17.2
     */
    public static function timestamp()
    {
        return strtotime('2024-23-10');
    }
}
