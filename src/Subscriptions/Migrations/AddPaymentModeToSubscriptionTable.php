<?php

namespace Give\Subscriptions\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give_Updates;

/**
 * @unreleased
 */
class AddPaymentModeToSubscriptionTable extends Migration
{
    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function title(): string
    {
        return 'Add payment mode column to subscription table';
    }

    public static function timestamp()
    {
        return strtotime('2022-11-30');
    }

    /**
     * Register background update.
     *
     * @unreleased
     *
     * @param Give_Updates $give_updates
     *
     */
    public function register(Give_Updates $give_updates)
    {
        $give_updates->register(
            [
                'id' => self::id(),
                'version' => '2.24.0',
                'callback' => [$this, 'run'],
            ]
        );
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function id(): string
    {
        return 'add_paymentmode_to_subscription_table';
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     *
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        if (! $this->hasPaymentModeColumn()) {
            $this->addPaymentModeColumn();
        }

        $this->processPaymentModeForExistingSubscriptions();
    }

    /**
     * Add payment mode column to subscription table.
     *
     * @unreleased
     *
     * @return void
     * @throws DatabaseMigrationException
     */
    private function addPaymentModeColumn()
    {
        global $wpdb;

        $subscriptionTableName = "{$wpdb->prefix}give_subscriptions";

        try {
            DB::query(
                "
                ALTER TABLE `$subscriptionTableName`
                    ADD COLUMN `payment_mode` varchar(20) NOT NULL
                    AFTER `parent_payment_id`;
            "
            );
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred adding the payment mode column to the subscription table',
                0, $exception);
        }
    }

    private function hasPaymentModeColumn()
    {
        global $wpdb;

        $subscriptionTableName = "{$wpdb->prefix}give_subscriptions";

        return 0 < DB::get_var(
            "
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '{$wpdb->dbname}'
                AND TABLE_NAME = '$subscriptionTableName'
                AND COLUMN_NAME = 'payment_mode';
        "
        );
    }

    /**
     * Process payment mode for existing subscriptions.
     *
     * @unreleased
     *
     * @return void
     * @throws DatabaseMigrationException
     */
    private function processPaymentModeForExistingSubscriptions()
    {
        global $wpdb;

        $subscriptionTableName = "{$wpdb->prefix}give_subscriptions";
        $donationMetaTableName = "{$wpdb->prefix}give_donationmeta";

        $give_updates = Give_Updates::get_instance();

        $perBatch = 500;

        $offset = ($give_updates->step - 1) * $perBatch;

        $result = DB::get_results(
            DB::prepare(
                "SELECT * FROM $subscriptionTableName LIMIT %d OFFSET %d",
                $perBatch,
                $offset
            )
        );

        $totalSubscriptions = DB::get_var("SELECT COUNT(id) FROM $subscriptionTableName");

        if ($result) {
            $give_updates->set_percentage(
                $totalSubscriptions,
                $give_updates->step * $perBatch
            );

            foreach ($result as $subscription) {
                // Get old parent payment id meta
                $paymentMode = DB::get_var(
                    DB::prepare("SELECT meta_value FROM $donationMetaTableName WHERE meta_key = '_give_payment_mode' AND donation_id = %d",
                        $subscription->parent_payment_id)
                );

                if ($paymentMode) {
                    try {
                        DB::update(
                            $subscriptionTableName,
                            ['payment_mode' => $paymentMode],
                            ['id' => $subscription->id],
                            ['%s'],
                            ['%d']
                        );
                    } catch (DatabaseQueryException $exception) {
                        $give_updates->__pause_db_update(true);
                        update_option('give_upgrade_error', 1, false);

                        throw new DatabaseMigrationException('An error occurred processing the payment mode for existing subscriptions',
                            0, $exception);

                    }
                }
            }
        } else {
            give_set_upgrade_complete(self::id());
        }
    }
}
