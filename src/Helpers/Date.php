<?php

namespace Give\Helpers;

/**
 * @since 2.20.0
 */
class Date {

    /**
     * Returns human readable date.
     *
     * @param string $datetime A date/time string
     * @since 2.20.0
     *
     * @return string
     */
    public static function getDateTime($datetime) {
        $dateTimestamp = strtotime($datetime);
        $currentTimestamp = current_time('timestamp');
        $todayTimestamp = strtotime('today', $currentTimestamp);
        $yesterdayTimestamp = strtotime('yesterday', $currentTimestamp);

        if ($dateTimestamp >= $todayTimestamp) {
            return sprintf(
                '%1$s %2$s %3$s',
                esc_html__('Today', 'give'),
                esc_html__('at', 'give'),
                date_i18n(get_option('time_format'), $dateTimestamp)
            );
        }

        if ($dateTimestamp >= $yesterdayTimestamp) {
            return sprintf(
                '%1$s %2$s %3$s',
                esc_html__('Yesterday', 'give'),
                esc_html__('at', 'give'),
                date_i18n(get_option('time_format'), $dateTimestamp)
            );
        }

        return sprintf(
            '%1$s %2$s %3$s',
            date_i18n(get_option('date_format'), $dateTimestamp),
            esc_html__('at', 'give'),
            date_i18n(get_option('time_format'), $dateTimestamp)
        );
    }
}
