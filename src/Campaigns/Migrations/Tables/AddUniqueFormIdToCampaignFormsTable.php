<?php

namespace Give\Campaigns\Migrations\Tables;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * A form belongs to one campaign. The table's primary key only stopped a form from being linked
 * to the same campaign twice, so a form could end up in several campaigns and whichever row came
 * first won. This removes the extra rows and makes form_id unique.
 *
 * @since TBD
 */
class AddUniqueFormIdToCampaignFormsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'give-campaigns-add-unique-form-id-to-campaign-forms-table';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Allow one campaign per form in give_campaign_forms';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2026-09-24 00:00:00');
    }

    /**
     * @inheritDoc
     *
     * @throws DatabaseMigrationException
     */
    public function run(): void
    {
        global $wpdb;

        $table = $wpdb->give_campaign_forms;
        $campaigns = $wpdb->give_campaigns;

        try {
            /*
             * For a form linked to several campaigns keep the campaign that lists it as its
             * default form, otherwise the lowest campaign id, which is the row that won before.
             */
            DB::query(
                "DELETE campaign_forms FROM $table AS campaign_forms
                INNER JOIN (
                    SELECT links.form_id,
                        COALESCE(
                            MIN(CASE WHEN campaigns.form_id = links.form_id THEN links.campaign_id END),
                            MIN(links.campaign_id)
                        ) AS keep_campaign_id
                    FROM $table AS links
                    LEFT JOIN $campaigns AS campaigns ON campaigns.id = links.campaign_id
                    GROUP BY links.form_id
                    HAVING COUNT(*) > 1
                ) AS keep ON keep.form_id = campaign_forms.form_id
                WHERE campaign_forms.campaign_id <> keep.keep_campaign_id"
            );

            $isUnique = DB::get_var(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table'
                AND INDEX_NAME = 'form_id' AND NON_UNIQUE = 0"
            );

            if ($isUnique) {
                return;
            }

            DB::query("ALTER TABLE $table DROP INDEX form_id, ADD UNIQUE KEY form_id (form_id)");
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while updating the $table table", 0, $exception);
        }
    }
}
