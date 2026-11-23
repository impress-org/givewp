<?php

namespace Give\Donations\Counters;

/**
 * The one place the donation list endpoints ask for a total (list count and stats).
 *
 * A total comes from one of two sources: a value counted elsewhere (the donation counters), or a
 * computation. A computed total is kept in a transient for a short time (60 seconds by default), so
 * how stale it can get is bounded by time, not by catching every write. Keeping the choice here
 * means that when the lists read their counts from the give_donations table, which replaces the
 * counters and the transient, only this class changes.
 *
 * Temporary: a stopgap until the donation lists read their counts from the give_donations table
 * (SOFT-5015)
 *
 * @since TBD
 *
 * @internal
 */
final class DonationTotals
{
    /**
     * @since TBD
     */
    public const TRANSIENT_PREFIX = 'give_donation_total_';

    /**
     * @since TBD
     */
    public const DEFAULT_TTL = 60;

    /**
     * Returns the total for the key.
     *
     * @since TBD
     *
     * @param string   $key      what the total depends on, usually the SQL that computes it
     * @param callable $compute  computes the total
     * @param mixed    $counted  an exact total counted elsewhere, or null when there is none
     * @param bool     $live     compute it now instead of reading the transient
     *
     * @return mixed
     */
    public static function resolve(string $key, callable $compute, $counted = null, bool $live = false)
    {
        if ($counted !== null) {
            return $counted;
        }

        $ttl = self::ttl();
        $name = self::transientName($key);

        if (!$live && $ttl > 0) {
            $stored = get_transient($name);

            if ($stored !== false) {
                return $stored;
            }
        }

        $value = $compute();

        if ($ttl > 0) {
            set_transient($name, $value, $ttl);
        }

        return $value;
    }

    /**
     * Seconds a computed total is kept. 0 keeps none, so every request computes it.
     *
     * @since TBD
     */
    private static function ttl(): int
    {
        /**
         * Filters how long, in seconds, a computed donation total is kept. 0 computes it on every request.
         *
         * Temporary stopgap, do not rely on it: it goes away when the donation lists read their
         * counts from the give_donations table (SOFT-5015).
         *
         * @since TBD
         *
         * @internal
         *
         * @param int $ttl
         */
        return max(0, (int)apply_filters('givewp_donation_totals_ttl', self::DEFAULT_TTL));
    }

    /**
     * @since TBD
     */
    private static function transientName(string $key): string
    {
        global $wpdb;

        // A prepared LIKE carries a placeholder for "%" that changes on every request.
        return self::TRANSIENT_PREFIX . md5($wpdb->remove_placeholder_escape($key));
    }
}
