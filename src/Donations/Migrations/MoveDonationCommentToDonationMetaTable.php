<?php

namespace Give\Donations\Migrations;

use Exception;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Log\Log;

/**
 * Class MoveDonationCommentToDonationMetaTable
 *
 * @unreleased
 */
class MoveDonationCommentToDonationMetaTable extends Migration
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function run()
    {
        $commentMetaTable = DB::prefix('give_commentmeta');
        $commentTable = DB::prefix('give_comments');
        $donationMetaTable = DB::prefix('give_donationmeta');

        DB::query('START TRANSACTION');

        try {
            $insertQuery = DB::query(
                "
                INSERT INTO $donationMetaTable (donation_id, meta_key, meta_value)
                SELECT
                    comment_parent,
                    '_give_donation_comment',
                    comment_content
                FROM
                    $commentTable
                WHERE
                    comment_type = 'donor_donation'
                "
            );

            if ( ! $insertQuery) {
                throw new Exception('Failed to insert donation comment into donation meta table.');
            }

            DB::query(
                "
                DELETE FROM $commentTable
                WHERE comment_type = 'donor_donation'
                    AND comment_parent IN(
                        SELECT
                            donation_id FROM $donationMetaTable
                        WHERE
                            meta_key = '_give_donation_comment')
                "
            );

            DB::query(
                "
                DELETE FROM $commentMetaTable
                WHERE give_comment_id IN(
                        SELECT
                            comment_ID FROM $commentTable
                        WHERE
                            comment_type = 'donor_donation'
                            AND comment_parent IN(
                                SELECT
                                    donation_id FROM $donationMetaTable
                                WHERE
                                    meta_key = '_give_donation_comment'))
                "
            );
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed running migration: ' . self::title());

            throw new $exception('Failed running migration: ' . self::title());
        }

        DB::query('COMMIT');
    }

    /**
     * @inheritdoc
     */
    public static function id()
    {
        return 'move-donation-comment-to-donation-meta-table';
    }

    /**
     * @inheritdoc
     */
    public static function title()
    {
        return 'Move donation comment to donation meta table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2023-03-27');
    }
}
