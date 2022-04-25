<?php

namespace Give\Log\Helpers;

/**
 * Helper class responsible for checking the environment.
 * @package Give\Log\Helpers
 *
 * @since 2.10.0
 */
class Environment
{
    /**
     * Check if current page is logs page.
     *
     * @return bool
     * @since 1.0.0
     */
    public static function isLogsPage()
    {
        if (!isset($_GET['page'], $_GET['tab'])) {
            return false;
        }

        if ('give-tools' === $_GET['page'] && 'logs' === $_GET['tab']) {
            return true;
        }

        return false;
    }

    /**
     * @since 2.18.0
     *
     * @return bool
     */
    public static function isWPDebugLogEnabled()
    {
        return defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
    }

    /**
     * @since 2.19.6
     *
     * @return bool
     */
    public static function isGiveDebugEnabled()
    {
        return defined('GIVE_DEBUG') && GIVE_DEBUG;
    }
}
