<?php

namespace Give\Donations\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * Creates the give_donations table, the purpose-built donation storage that will replace the
 * give_payment post type and its meta. The table is created empty and nothing reads from it yet.
 *
 * A column exists for every field the engine filters, sorts, joins or sums on, plus the
 * transaction record (who the donor said they were and what was charged). Operational detail
 * only ever read back per donation stays in give_donationmeta.
 *
 * Amounts are in minor units. amount_intended and amount_intended_base are stored generated
 * columns, so the database keeps them correct when a fee changes and they can be indexed. The
 * covering indexes end in amount_intended_base so campaign, form, donor and timeline totals are
 * answered from the index alone.
 *
 * dbDelta() does not reliably parse GENERATED ALWAYS AS, so this is a raw CREATE TABLE.
 *
 * Once the table exists the storage flag moves from posts to syncing, which switches the
 * dual-write on. Reads stay on posts until a site migrates and switches.
 *
 * @since TBD
 */
class CreateDonationsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'create_donations_table';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Create the give_donations table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2026-10-20 00:00:00');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        // Resolved from the current prefix, not the cached $wpdb property: a network-wide install
        // switches blogs before running upgrades and the property is not refreshed.
        $table = DB::prefix('give_donations');
        $charset = DB::get_charset_collate();

        // id is the legacy post ID, with no AUTO_INCREMENT, while the two storages are kept in sync.
        //
        // The two generated columns are signed, and both operands are cast before subtracting. An
        // admin can edit a donation's total below its recovered fee, legacy storage accepts that,
        // and the legacy intended-amount sum goes negative with it. Unsigned arithmetic would
        // reject the row under strict SQL mode and silently store 0 without it, so signed is the
        // only choice that keeps both storages equal.
        //
        // sequential_number is a plain index, not UNIQUE, even though give_sequential_ordering makes
        // it unique in practice. The sync upserts with ON DUPLICATE KEY UPDATE, and a second unique
        // key would let a stale row that still carries a reused number be overwritten with another
        // donation's data instead of that donation getting its own row.
        //
        // Index prefixes on the two VARCHAR(255) columns keep each key under the 767-byte limit of
        // older InnoDB row formats under utf8mb4, the same reason WordPress core indexes
        // post_name(191).
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED NOT NULL,
            donor_id BIGINT UNSIGNED NOT NULL,
            campaign_id BIGINT UNSIGNED NULL,
            form_id BIGINT UNSIGNED NULL,
            subscription_id BIGINT UNSIGNED NULL,
            status VARCHAR(20) NOT NULL,
            type VARCHAR(20) NOT NULL,
            mode VARCHAR(4) NOT NULL,
            gateway_id VARCHAR(64) NOT NULL,
            gateway_transaction_id VARCHAR(255) NULL,
            amount_total BIGINT UNSIGNED NOT NULL,
            fee_amount_recovered BIGINT UNSIGNED NULL,
            currency CHAR(3) NOT NULL,
            base_currency CHAR(3) NOT NULL,
            exchange_rate DECIMAL(20,10) NOT NULL DEFAULT 1,
            amount_intended BIGINT GENERATED ALWAYS AS (CAST(amount_total AS SIGNED) - CAST(IFNULL(fee_amount_recovered, 0) AS SIGNED)) STORED,
            amount_intended_base BIGINT GENERATED ALWAYS AS (ROUND((CAST(amount_total AS SIGNED) - CAST(IFNULL(fee_amount_recovered, 0) AS SIGNED)) / exchange_rate)) STORED,
            sequential_number BIGINT UNSIGNED NULL,
            purchase_key VARCHAR(64) NULL,
            email VARCHAR(255) NOT NULL,
            honorific VARCHAR(20) NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NULL,
            phone VARCHAR(50) NULL,
            company VARCHAR(255) NULL,
            billing_address1 VARCHAR(255) NULL,
            billing_address2 VARCHAR(255) NULL,
            billing_city VARCHAR(255) NULL,
            billing_state VARCHAR(255) NULL,
            billing_zip VARCHAR(50) NULL,
            billing_country CHAR(2) NULL,
            anonymous TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY sequential_number (sequential_number),
            KEY campaign_totals (status, mode, campaign_id, donor_id, amount_intended_base),
            KEY timeline_totals (status, mode, created_at, amount_intended_base),
            KEY donor_totals (donor_id, status, mode, created_at, amount_intended_base),
            KEY form_totals (status, mode, form_id, donor_id, amount_intended_base),
            KEY mode_amount_total (mode, amount_total),
            KEY amount_intended_base (amount_intended_base),
            KEY subscription_payments (subscription_id, type, created_at),
            KEY gateway_id (gateway_id),
            KEY gateway_transaction_id (gateway_transaction_id(191)),
            KEY purchase_key (purchase_key),
            KEY email (email(191))
        ) {$charset}";

        try {
            DB::query($sql);
        } catch (DatabaseQueryException $exception) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- the previous exception cannot be escaped
            throw new DatabaseMigrationException(esc_html("An error occurred while creating the {$table} table"), 0, $exception);
        }

        // The table exists, so from here every donation write is mirrored into it. A site that is
        // already past syncing keeps its state. Option name and values are spelled out here rather
        // than read from DonationStorageFlag: a migration must keep working as it was written even
        // after the production classes move on.
        if (get_option('give_donation_storage', 'posts') === 'posts') {
            update_option('give_donation_storage', 'syncing');
        }
    }
}
