<?php

namespace Give\Campaigns\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Drops the campaign stats cache so it is rebuilt from scratch. Before this migration the
 * subscriptions cache was written under one option name and read under another, and the
 * rebuild migration merged donation rows into it, so what is stored cannot be trusted.
 * The repository repopulates the cache on demand.
 *
 * @since 4.18.0
 */
class FlushCampaignsDataCache extends Migration
{
    /**
     * @since 4.18.0
     */
    public static function id(): string
    {
        return 'flush_campaigns_data_cache';
    }

    /**
     * @since 4.18.0
     */
    public static function title(): string
    {
        return 'Flush campaign stats cache';
    }

    /**
     * @since 4.18.0
     */
    public static function timestamp(): string
    {
        return strtotime('2026-09-21 00:00:00');
    }

    /**
     * @since 4.18.0
     */
    public function run()
    {
        delete_option('give_campaigns_data');
        delete_option('give_campaigns_subscriptions_data');
        delete_option('give_campaigns_subscription_data');
    }
}
