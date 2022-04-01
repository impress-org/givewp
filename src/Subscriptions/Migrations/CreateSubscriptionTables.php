<?php

namespace Give\Subscriptions\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

class CreateSubscriptionTables extends Migration
{
    public static function id()
    {
        return 'create_subscription_tables';
    }

    public function run()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $subscriptionTableName = "{$wpdb->prefix}give_subscriptions";
        $metaTableName = "{$wpdb->prefix}give_subscriptionmeta";

        try {
            DB::delta(
                "
                CREATE TABLE `$subscriptionTableName` (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `customer_id` bigint(20) NOT NULL,
                    `period` varchar(20) NOT NULL,
                    `frequency` bigint(20) NOT NULL DEFAULT '1',
                    `initial_amount` decimal(18,10) NOT NULL,
                    `recurring_amount` decimal(18,10) NOT NULL,
                    `recurring_fee_amount` decimal(18,10) NOT NULL,
                    `bill_times` bigint(20) NOT NULL,
                    `transaction_id` varchar(60) NOT NULL,
                    `parent_payment_id` bigint(20) NOT NULL,
                    `product_id` bigint(20) NOT NULL,
                    `created` datetime NOT NULL,
                    `expiration` datetime NOT NULL,
                    `status` varchar(20) NOT NULL,
                    `profile_id` varchar(60) NOT NULL,
                    `notes` longtext NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `profile_id` (`profile_id`),
                    KEY `customer` (`customer_id`),
                    KEY `transaction` (`transaction_id`),
                    KEY `customer_and_status` (`customer_id`,`status`)
                ) $charset_collate;
            "
            );

            DB::delta(
                "
                CREATE TABLE `$metaTableName` (
                    `meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `subscription_id` bigint(20) NOT NULL,
                    `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                    `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
                    PRIMARY KEY (`meta_id`),
                    KEY `subscription_id` (`subscription_id`),
                    KEY `meta_key` (`meta_key`(191))
                ) $charset_collate;
            "
            );
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred creating the subscription tables', 0, $exception);
        }
    }

    public static function timestamp()
    {
        return strtotime('2022-03-30');
    }
}
