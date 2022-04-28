<?php

namespace Give\PaymentGateways\PayPalCommerce\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
 */
class RemoveLogWithCardInfoInLegacyPaymentData extends Migration
{
    /**
     * @inheritDoc
     */
    public function run()
    {
        $tableName = DB::prefix('give_log');

        DB::query(
            "
            DELETE FROM {$tableName}
            WHERE data like '%card_%'
            "
        );
    }

    /**
     * @inerhitDoc
     */
    public static function id()
    {
        return 'remove-log-with-card-info-in-legacy-payment-data';
    }

    /**
     * @inerhitDoc
     */
    public static function timestamp()
    {
        return strtotime('2022-04-28');
    }

    /**
     * @inerhitDoc
     */
    public static function title()
    {
        return 'Remove Log With CardInfo In Legacy Payment Data';
    }
}
