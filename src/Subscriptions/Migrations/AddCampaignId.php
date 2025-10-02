<?php

namespace Give\Subscriptions\Migrations;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Contracts\ReversibleMigration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 */
class AddCampaignId extends Migration implements ReversibleMigration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'add_campaign_id_to_subscriptions';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Add campaign id to subscriptions';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-10-02 00:00:00');
    }

    /**
     * @inheritDoc
     *
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        try {
            $query = <<<SQL
                UPDATE %s AS subscriptions
                JOIN %s donations
                    ON subscriptions.parent_payment_id = donations.ID
                SET subscriptions.campaign_id = (SELECT meta_value FROM %s WHERE donation_id = donations.ID AND meta_key = '%s' LIMIT 1)
            SQL;

            DB::query(sprintf(
                $query,
                DB::prefix('give_subscriptions'),
                DB::prefix('posts'),
                DB::prefix('give_donationmeta'),
                DonationMetaKeys::CAMPAIGN_ID
            ));
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while adding campaign ID to the give_subscriptions table",
                0, $exception);
        }
    }


    /**
     * @inheritDoc
     */
    public function reverse(): void
    {
        DB::table('give_subscriptions')->update(['campaign_id' => null]);
    }
}
