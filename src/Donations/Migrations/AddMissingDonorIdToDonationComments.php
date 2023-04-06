<?php

namespace Give\Donations\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class AddMissingDonorIdToDonationComment
 *
 * @since 2.24.0
 */
class AddMissingDonorIdToDonationComments extends Migration
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        $commentMetaTable = DB::prefix('give_commentmeta');
        $commentTable = DB::prefix('give_comments');
        $donationMetaTable = DB::prefix('give_donationmeta');

        DB::query(
            "
            UPDATE
                 $commentMetaTable AS cm
                INNER JOIN $commentTable AS c ON c.comment_ID = cm.give_comment_id
                INNER JOIN $donationMetaTable AS dm ON c.comment_parent = dm.donation_id
            SET
                cm.meta_value = dm.meta_value
            WHERE
                dm.meta_key = '_give_payment_donor_id'
                AND cm.meta_key = '_give_donor_id'
                AND(cm.meta_value IS NULL OR cm.meta_value = '')
            "
        );
    }

    /**
     * @inheritdoc
     */
    public static function id()
    {
        return 'add-missing-donor-id-in-donation-comments';
    }

    /**
     * @inheritdoc
     */
    public static function title()
    {
        return 'Add missing donor id in donation comments';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2022-19-12');
    }
}
