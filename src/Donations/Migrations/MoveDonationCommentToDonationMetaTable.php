<?php

namespace Give\Donations\Migrations;

use Exception;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Log\Log;

/**
 * Class MoveDonationCommentToDonationMetaTable
 *
 * @since 2.27.0
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

        $commentsCount = DB::get_var(
            "
            SELECT
                COUNT(1)
            FROM
                $commentTable
            WHERE
                comment_type = 'donor_donation'
            "
        );

        if (!intval($commentsCount)) {
            return;
        }

        DB::beginTransaction();

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

            if (!$insertQuery) {
                throw new Exception('Failed to insert donation comment into donation meta table.');
            }

            DB::query(
                "
                DELETE FROM $commentMetaTable
                WHERE give_comment_id IN(
                    SELECT
                        comment_ID FROM $commentTable
                    WHERE
                        comment_type = 'donor_donation'
                )
                "
            );

            DB::query(
                "
                DELETE FROM $commentTable
                WHERE comment_type = 'donor_donation'
                "
            );
        } catch (Exception $exception) {
            DB::rollback();

            Log::error('Failed running migration: ' . self::title());

            throw new DatabaseMigrationException('Failed running migration: ' . self::title(), 0, $exception);
        }

        DB::commit();
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
