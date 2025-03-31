<?php

namespace Give\MigrationLog;

/**
 * Class MigrationLogStatus
 * @package Give\MigrationLog
 *
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

    /**
     * Get default migration status
     *
     * @return string
     */
    public static function getDefault()
    {
        return MigrationLogStatus::FAILED;
    }

    /**
     * Get all migration statuses
     *
     * @return array
     */
    public static function getAll()
    {
        return [
            MigrationLogStatus::SUCCESS => esc_html__('Success', 'give'),
            MigrationLogStatus::FAILED => esc_html__('Failed', 'give'),
            MigrationLogStatus::PENDING => esc_html__('Pending', 'give'),
            MigrationLogStatus::RUNNING => esc_html__('Running', 'give'),
            MigrationLogStatus::INCOMPLETE => esc_html__('Incomplete', 'give'),
        ];
    }

    /**
     * Check if value is a valid migration status
     *
     * @param string $status
     *
     * @return bool
     */
    public static function isValid($status)
    {
        return array_key_exists($status, self::getAll());
    }
}
