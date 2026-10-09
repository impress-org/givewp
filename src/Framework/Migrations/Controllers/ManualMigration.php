<?php

namespace Give\Framework\Migrations\Controllers;

use Exception;
use Give\Framework\Migrations\Actions\ClearCompletedUpgrade;
use Give\Framework\Migrations\Actions\ManuallyRunMigration;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Framework\Permissions\Facades\UserPermissions;

/**
 * Class ManualMigration
 *
 * Handles and admin request to manually trigger migrations
 *
 * @since 2.9.2
 */
class ManualMigration
{
    /**
     * @var MigrationsRegister
     */
    private $migrationsRegister;

    /**
     * ManualMigration constructor.
     *
     * @since 2.9.2
     *
     * @param MigrationsRegister $migrationsRegister
     *
     */
    public function __construct(MigrationsRegister $migrationsRegister)
    {
        $this->migrationsRegister = $migrationsRegister;
    }

    /**
     * @since TBD Verify the nonce and unslash params.
     * @since 3.19.0 sanitize params
     * @since 2.9.2
     */
    public function __invoke()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- presence check only; the nonce is verified below before any param is used.
        if (empty($_GET['give-run-migration']) && empty($_GET['give-clear-update'])) {
            return;
        }

        if ( ! UserPermissions::settings()->canManage()) {
            give()->notices->register_notice(
                [
                    'id' => 'invalid-migration-permissions',
                    'description' => 'You do not have the permissions to manually run or clear migrations',
                ]
            );

            return;
        }

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ( ! wp_verify_nonce($nonce, 'give_manual_migration')) {
            give()->notices->register_notice(
                [
                    'id' => 'invalid-migration-nonce',
                    'description' => 'This migration link has expired or is not valid. Please use a new link.',
                ]
            );

            return;
        }

        if ( ! empty($_GET['give-run-migration'])) {
            $this->runMigration(sanitize_text_field(wp_unslash($_GET['give-run-migration'])));
        }

        if ( ! empty($_GET['give-clear-update'])) {
            $this->clearMigration(sanitize_text_field(wp_unslash($_GET['give-clear-update'])));
        }
    }

    /**
     * Runs the given automatic migration
     *
     * @since 2.9.2
     *
     * @param string $migrationId
     */
    private function runMigration($migrationId)
    {
        if ( ! $this->migrationsRegister->hasMigration($migrationId)) {
            give()->notices->register_notice(
                [
                    'id' => 'invalid-migration-id',
                    'description' => "There is no migration with the ID: {$migrationId}",
                ]
            );

            return;
        }

        /** @var Migration $migration */
        $migration = give($this->migrationsRegister->getMigration($migrationId));

        /** @var ManuallyRunMigration $manualRunner */
        $manualRunner = give(ManuallyRunMigration::class);

        try {
            $manualRunner($migration);

            give()->notices->register_notice(
                [
                    'id' => 'automatic-migration-run',
                    'type' => 'success',
                    'description' => "The {$migrationId} migration was manually triggered",
                ]
            );
        } catch (Exception $exception) {
            give()->notices->register_notice(
                [
                    'id' => 'automatic-migration-run-failure',
                    'description' => "The manually triggered {$migrationId} migration ran but failed",
                ]
            );
        }
    }

    /**
     * Clears the manual migration so it may be run again
     *
     * @since 2.9.2
     *
     * @param string $migrationToClear
     */
    private function clearMigration($migrationToClear)
    {
        /** @var ClearCompletedUpgrade $clearUpgrade */
        $clearUpgrade = give(ClearCompletedUpgrade::class);

        try {
            $clearUpgrade($migrationToClear);
        } catch (Exception $exception) {
            give()->notices->register_notice(
                [
                    'id' => 'clear-migration-failed',
                    'description' => "Unable to reset migration. Error: {$exception->getMessage()}",
                ]
            );

            return;
        }

        give()->notices->register_notice(
            [
                'id' => 'automatic-migration-cleared',
                'type' => 'success',
                'description' => "The {$migrationToClear} update was cleared and may be run again.",
            ]
        );
    }
}
