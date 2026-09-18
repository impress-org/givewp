<?php

namespace Give\DonationForms\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Dedupe form stat meta rows and add a composite index.
 *
 * Forms that ran a stat recalc without a wp_postmeta mirror ended up
 * with one wp_give_formmeta row per recalc, growing the (form_id, meta_key)
 * row count into the hundreds. The V2 DonationFormsRepository joins the
 * formmeta table once per meta key under SELECT DISTINCT, so the
 * duplicates explode into a Cartesian product and the Donation Forms
 * admin list slows to minutes.
 *
 * This migration:
 *   1. Collapses duplicates for the three stat meta keys, keeping the
 *      newest row by meta_id.
 *   2. Adds a composite index on (form_id, meta_key(191)) so the join
 *      uses an index seek instead of a scan.
 *
 * The meta_key(191) prefix keeps the index under MySQL's 3072-byte key
 * length limit on utf8mb4 (191 * 4 + 8 = 772 bytes).
 *
 * @unreleased
 */
class FormStatMetaDedupeAndIndex extends Migration
{
    /**
     * @unreleased
     */
    public static function id()
    {
        return 'donation-forms-form-stat-meta-dedupe-and-index';
    }

    /**
     * @unreleased
     */
    public static function title()
    {
        return 'Dedupe form stat meta and add composite index';
    }

    /**
     * @unreleased
     */
    public static function timestamp()
    {
        return strtotime('2026-07-18');
    }

    /**
     * @unreleased
     */
    public function run()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'give_formmeta';
        $statKeys = [
            '_give_form_earnings',
            '_give_form_sales',
            '_give_form_goal_progress',
        ];

        $placeholders = implode(',', array_fill(0, count($statKeys), '%s'));

        // 1. Dedupe: keep the newest row per (form_id, meta_key), delete the rest.
        $wpdb->query(
            $wpdb->prepare(
                "DELETE fm FROM {$table} fm
                JOIN {$table} keeper
                  ON keeper.form_id = fm.form_id
                 AND keeper.meta_key = fm.meta_key
                 AND keeper.meta_id > fm.meta_id
                WHERE fm.meta_key IN ({$placeholders})",
                $statKeys
            )
        );

        // 2. Composite index for the attachMeta join.
        $indexName = 'form_id_meta_key';
        $hasIndex = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW INDEX FROM {$table} WHERE Key_name = %s",
                $indexName
            )
        );

        if ( ! $hasIndex ) {
            $wpdb->query(
                "ALTER TABLE {$table}
                ADD INDEX {$indexName} (form_id, meta_key(191))"
            );
        }
    }
}
