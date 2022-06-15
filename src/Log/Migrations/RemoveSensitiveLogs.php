<?php

declare(strict_types=1);

namespace Give\Log\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Log\Log;

/**
 * Removes logs that contain sensitive information.
 *
 * @since 2.20.0
 */
class RemoveSensitiveLogs extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'remove_sensitive_logs';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Remove logs wth sensitive information';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2022-05-04');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $tableName = DB::prefix('give_log');
        $redactions = implode('|', Log::getRedactionList());

        DB::query(" DELETE FROM $tableName WHERE data REGEXP '$redactions' ");
    }
}
