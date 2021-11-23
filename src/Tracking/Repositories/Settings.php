<?php

namespace Give\Tracking\Repositories;

/**
 * Class Settings
 * @package Give\Tracking\Repositories
 *
 * @since 2.10.0
 */
class Settings
{
    const USAGE_TRACKING_OPTION_KEY = 'usage_tracking';
    const USAGE_TRACKING_NOTICE_NAG_OPTION_KEY = 'give_telemetry_hide_usage_tracking_notice';

    /**
     * Return "usage_tracking" give setting option value.
     *
     * @since 2.10.0
     * @return string
     */
    public function getUsageTrackingOptionValue()
    {
        return give_get_option(self::USAGE_TRACKING_OPTION_KEY, 'disabled');
    }

    /**
     * Get "give_telemetry_hide_usage_tracking_notice" option value.
     *
     * @since 2.10.0
     *
     * @return string
     */
    public function getUsageTrackingNoticeNagOptionValue()
    {
        return get_option(self::USAGE_TRACKING_NOTICE_NAG_OPTION_KEY, null);
    }

    /**
     * Store "usage_tracking" give setting option value.
     *
     * @since 2.10.0
     *
     * @param $optionValue
     *
     * @return boolean
     */
    public function saveUsageTrackingOptionValue($optionValue)
    {
        return give_update_option(self::USAGE_TRACKING_OPTION_KEY, $optionValue);
    }

    /**
     * Store "give_hide_usage_tracking_notice" option value.
     *
     * @since 2.10.0
     *
     * @param int $optionValue
     *
     * @return string
     */
    public function saveUsageTrackingNoticeNagOptionValue($optionValue)
    {
        return update_option(self::USAGE_TRACKING_NOTICE_NAG_OPTION_KEY, $optionValue, false);
    }
}
