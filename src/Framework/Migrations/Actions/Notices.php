<?php

namespace Give\Framework\Migrations\Actions;

use Give\Framework\Migrations\MigrationsRegister;
use Give\MigrationLog\MigrationLogRepository;
use Give\MigrationLog\MigrationLogStatus;

/**
 * @since 4.3.0
 *
 * Show reversed migration notice
 */
class Notices
{
    public function __invoke()
    {
        $migrations = give(MigrationLogRepository::class)->getMigrationsByStatus(MigrationLogStatus::REVERSED);

        foreach ($migrations as $migration) {
            $migrationClass = give(MigrationsRegister::class)->getMigration($migration->getId());

            $listTableLink = sprintf(
                '<a href="%s">%s</a>',
                admin_url('edit.php?post_type=give_forms&page=give-tools&tab=data'),
                esc_html__('Run update', 'give')
            );

            give()->notices->register_notice(
                [
                    'id' => $migration->getId(),
                    'type' => 'warning',
                    'description' => sprintf(
                        __('<strong>GiveWP</strong> Pending database update: "%s". %s', 'give'),
                        $migrationClass::title(),
                        $listTableLink
                    ),
                ]
            );
        }
    }
}
