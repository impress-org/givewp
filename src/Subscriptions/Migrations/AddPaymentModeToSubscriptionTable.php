<?php

namespace Give\Subscriptions\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give_Updates;

/**
 * @since 2.24.0
 */
class AddPaymentModeToSubscriptionTable extends Migration
{
    /**
     * @inheritDoc
     *
     * @since 2.24.0
     */
    public static function title(): string
    {
        return 'Add payment mode column to subscription table';
    }

    /**
     * @inheritDoc
     *
     * @since 2.24.0
     */
    public static function timestamp()
    {
        return strtotime('2022-11-30');
    }

    /**
     * @inheritDoc
     *
     * @since 2.24.0
     */
    public static function id(): string
    {
        return 'add_paymentmode_to_subscription_table';
    }

    /**
     * @inheritDoc
     *
     * @since 2.24.0
     *
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        $this->addPaymentModeColumn();
        $this->processPaymentModeForExistingSubscriptions();
    }

    /**
     * Add payment mode column to subscription table.
     *
     * @since 2.24.0
     *
     * @return void
     * @throws DatabaseMigrationException
     */
    private function addPaymentModeColumn()
    {
        global $wpdb;

        $subscriptionTableName = "{$wpdb->prefix}give_subscriptions";

        try {
            maybe_add_column(
                $subscriptionTableName,
                'payment_mode',
                "ALTER TABLE `$subscriptionTableName` ADD COLUMN `payment_mode` varchar(20) NOT NULL DEFAULT '' AFTER `parent_payment_id`"
            );
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred adding the payment mode column to the subscription table',
                0, $exception);
        }
    }

    /**
     * Process payment mode for existing subscriptions.
     *
     * @since 2.24.0
     *
     * @return void
     * @throws DatabaseMigrationException
     */
    private function processPaymentModeForExistingSubscriptions()
    {
        global $wpdb;

        $subscriptionTableName = "{$wpdb->prefix}give_subscriptions";
        $donationMetaTableName = "{$wpdb->prefix}give_donationmeta";

        try {
            DB::query(
                "
                UPDATE
                    $subscriptionTableName subscription
                    LEFT JOIN $donationMetaTableName donationMeta ON subscription.parent_payment_id = donationMeta.donation_id
                SET
                    subscription.payment_mode = donationMeta.meta_value
                WHERE
                    donationMeta.meta_key = '_give_payment_mode'
            "
            );
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred processing the payment mode for existing subscriptions',
                0, $exception);
        }
    }
}
