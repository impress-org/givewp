<?php

namespace Give\Campaigns\Migrations\RevenueTable;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 */
class AssociateDonationsToCampaign extends Migration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'associate_donations_to_campaign';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Associate donations to campaign';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2024-10-14 00:00:01');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        try {
            DB::query("UPDATE {$wpdb->give_revenue} AS revenue JOIN {$wpdb->give_campaign_forms} forms ON revenue.form_id = forms.form_id SET revenue.campaign_id = forms.campaign_id");
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while updating the {$wpdb->give_revenue} table", 0,
                $exception);
        }
    }
}
