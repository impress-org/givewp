<?php

namespace Give\DonationForms\AsyncData\AdminFormListView;

/**
 * @unreleased
 */
class AdminFormListViewOptions
{
    /**
     * @unreleased
     */
    public static function isAllStatsColumnsAsync(): bool
    {
        if ( ! defined('GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS')) {
            define("GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS", true);
        }

        return (bool)GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS;
    }

    /**
     * @unreleased
     */
    public static function isGoalColumnAsync(): bool
    {
        if (defined('GIVE_IS_GOAL_COLUMN_ASYNC_ON_ADMIN_FORM_LIST_VIEWS')) {
            return (bool)GIVE_IS_GOAL_COLUMN_ASYNC_ON_ADMIN_FORM_LIST_VIEWS;
        }

        return self::isAllStatsColumnsAsync();
    }

    /**
     * @unreleased
     */
    public static function isDonationColumnAsync(): bool
    {
        if (defined('GIVE_IS_DONATIONS_COLUMN_ASYNC_ON_ADMIN_FORM_LIST_VIEWS')) {
            return (bool)GIVE_IS_DONATIONS_COLUMN_ASYNC_ON_ADMIN_FORM_LIST_VIEWS;
        }

        return self::isAllStatsColumnsAsync();
    }

    /**
     * @unreleased
     */
    public static function isRevenueColumnAsync(): bool
    {
        if (defined('GIVE_IS_REVENUE_COLUMN_ASYNC_ON_ADMIN_FORM_LIST_VIEWS')) {
            return (bool)GIVE_IS_REVENUE_COLUMN_ASYNC_ON_ADMIN_FORM_LIST_VIEWS;
        }

        return self::isAllStatsColumnsAsync();
    }

    /**
     * @unreleased
     */
    public static function useCachedMetaKeys()
    {
        return apply_filters('givewp_use_cached_form_stats_meta_keys_on_admin_form_list_views', false);
    }
}
