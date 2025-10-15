<?php

namespace Give\Subscriptions\Migrations;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 *
 * During the v2 form data transfer to the v3 form
 * we update the _give_payment_form_id in donations meta table to reference the new v3 form id
 * but that update was not reflected in give_subscriptions.product_id prior to this migration
 *
 * This migration will update all subscriptions that do not have the correct product_id value
 */
class UpdateProductID extends Migration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'update_transferred_subscriptions_product_id';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Update subscriptions to reflect the v2 form data transfer to the v3 form';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-10-15 00:00:00');
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
                INNER JOIN %s donationMeta
                    ON donationMeta.meta_key = '%s'
                    AND donationMeta.meta_value = subscriptions.id
                INNER JOIN %s donationMeta2
                    ON donationMeta2.donation_id = donationMeta.donation_id
                    AND donationMeta2.meta_key = '%s'
                SET subscriptions.product_id = donationMeta2.meta_value
                WHERE subscriptions.product_id != donationMeta2.meta_value
            SQL;

            DB::query(sprintf(
                $query,
                DB::prefix('give_subscriptions'),
                DB::prefix('give_donationmeta'),
                DonationMetaKeys::SUBSCRIPTION_ID,
                DB::prefix('give_donationmeta'),
                DonationMetaKeys::FORM_ID,
            ));
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while updating the give_subscriptions table",
                0, $exception);
        }
    }
}
