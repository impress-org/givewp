<?php

namespace Give\Log\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
 */
class CompleteRemovedLegacyLogMigration extends Migration
{
    /**
     * @unreleased
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'complete-removed-legacy-log-migration';
    }

    /**
     * @unreleased
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2022-06-16');
    }

    /**
     * @unreleased
     * @inheritdoc
     */
    public static function title()
    {
        return esc_html__('Complete Removed Legacy Log Migration', 'give');
    }

    /**
     * @unreleased
     * @inheritdoc
     */
    public function run()
    {
        give_set_upgrade_complete('v20_logs_upgrades');
    }
}
