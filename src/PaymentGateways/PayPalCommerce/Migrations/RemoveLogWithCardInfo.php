<?php

namespace Give\PaymentGateways\PayPalCommerce\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * @since 2.19.7
 */
class RemoveLogWithCardInfo extends Migration
{
    /**
     * @inheritDoc
     */
    public function run()
    {
        global $wpdb;

        DB::query(
            "
            DELETE FROM {$wpdb->prefix}give_log
            WHERE data like '%cardInfo%'
            "
        );
    }

    /**
     * @inerhitDoc
     */
    public static function id()
    {
        return 'remove-log-with-card-info';
    }

    /**
     * @inerhitDoc
     */
    public static function timestamp()
    {
        return strtotime('2022-03-25');
    }

    /**
     * @inerhitDoc
     */
    public static function title()
    {
        return 'Remove Log With CardInfo';
    }
}
