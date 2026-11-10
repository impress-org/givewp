<?php

namespace Give\MigrationLog\Helpers;

/**
 * Class Environment
 * @package Give\MigrationLog\Helpers
 *
 * @since 2.10.0
 */
class Environment
{
    /**
     * Check if current page is database updates page.
     *
     * @return bool
     */
    public static function isMigrationsPage()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only page, tab and section checks; they only decide whether this is the migrations page.
        if ( ! isset($_GET['page'], $_GET['tab'])) {
            return false;
        }

        if (
            'give-tools' === $_GET['page']
            && 'data' === $_GET['tab']
            && ( ! isset($_GET['section']) || 'database_updates' === $_GET['section'])
        ) {
            return true;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        return false;
    }
}
