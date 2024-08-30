<?php

namespace Give\DonationForms\AsyncData\FormGrid;

/**
 * @since 3.16.0
 */
class FormGridViewOptions
{
    /**
     * @since 3.16.0
     */
    public static function isAllProgressBarStatsAsync(): bool
    {
        if ( ! defined('GIVE_IS_ALL_PROGRESS_BAR_STATS_ASYNC_ON_FORM_GRID')) {
            define("GIVE_IS_ALL_PROGRESS_BAR_STATS_ASYNC_ON_FORM_GRID", true);
        }

        return (bool)GIVE_IS_ALL_PROGRESS_BAR_STATS_ASYNC_ON_FORM_GRID;
    }

    /**
     * @since 3.16.0
     */
    public static function isProgressBarAmountRaisedAsync(): bool
    {
        if (defined('GIVE_IS_PROGRESS_BAR_AMOUNT_RAISED_ASYNC_ON_FORM_GRID')) {
            return (bool)GIVE_IS_PROGRESS_BAR_AMOUNT_RAISED_ASYNC_ON_FORM_GRID;
        }

        return self::isAllProgressBarStatsAsync();
    }

    /**
     * @since 3.16.0
     */
    public static function isProgressBarDonationsCountAsync(): bool
    {
        if (defined('GIVE_IS_PROGRESS_BAR_DONATIONS_COUNT_ASYNC_ON_FORM_GRID')) {
            return (bool)GIVE_IS_PROGRESS_BAR_DONATIONS_COUNT_ASYNC_ON_FORM_GRID;
        }

        return self::isAllProgressBarStatsAsync();
    }

    /**
     * @since 3.16.0
     */
    public static function useCachedMetaKeys()
    {
        return apply_filters('givewp_use_cached_form_stats_meta_keys_on_form_grid', false);
    }
}
