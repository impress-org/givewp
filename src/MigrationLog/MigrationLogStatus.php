<?php

namespace Give\MigrationLog;

/**
 * Class MigrationLogStatus
 * @package Give\MigrationLog
 *
 * @since 4.3.0 add REVERSED status
 * @since 4.0.0 add RUNNING and INCOMPLETE statuses
 * @since 2.10.0
 */
class MigrationLogStatus
{
    const SUCCESS = 'success';
    const FAILED = 'failed';
    const PENDING = 'pending';
    const RUNNING = 'running';
    const INCOMPLETE = 'incomplete';
    const REVERSED = 'reversed';

    /**
     * Get default migration status
     */
    public static function getDefault(): string
    {
        return MigrationLogStatus::FAILED;
    }

    /**
     * Get all migration statuses
     */
    public static function getAll(): array
    {
        return [
            MigrationLogStatus::SUCCESS => esc_html__('Success', 'give'),
            MigrationLogStatus::FAILED => esc_html__('Failed', 'give'),
            MigrationLogStatus::PENDING => esc_html__('Pending', 'give'),
            MigrationLogStatus::RUNNING => esc_html__('Running', 'give'),
            MigrationLogStatus::INCOMPLETE => esc_html__('Incomplete', 'give'),
            MigrationLogStatus::REVERSED => esc_html__('Pending', 'give'),
        ];
    }

    /**
     * Check if value is a valid migration status
     */
    public static function isValid(string $status): bool
    {
        return array_key_exists($status, self::getAll());
    }
}
