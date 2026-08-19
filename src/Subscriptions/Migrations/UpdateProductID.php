<?php

namespace Give\Subscriptions\Migrations;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @since 4.12.0
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
                INNER JOIN %s revenue
                    ON subscriptions.parent_payment_id = revenue.donation_id
                SET subscriptions.product_id = revenue.form_id
                WHERE subscriptions.product_id != revenue.form_id
            SQL;

            DB::query(sprintf(
                $query,
                DB::prefix('give_subscriptions'),
                DB::prefix('give_revenue')
            ));
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while updating the give_subscriptions table",
                0, $exception);
        }
    }
}
