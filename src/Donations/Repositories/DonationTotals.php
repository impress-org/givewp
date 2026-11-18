<?php

namespace Give\Donations\Repositories;

/**
 * The one place the donation list endpoints ask for a total (list count and stats).
 *
 * A total comes from one of three sources: a value counted elsewhere (the donation counters), a
 * live computation, or the totals cache. Keeping the choice here means that when the lists read
 * their counts from the give_donations table, which replaces the counters and the cache, only this
 * class changes.
 *
 * Temporary: a stopgap until donations read from the give_donations table (SOFT-5015)
 *
 * @since TBD
 *
 * @internal
 */
final class DonationTotals
{
    /**
     * Returns the total for the key.
     *
     * @since TBD
     *
     * @param string   $key      what the total depends on, usually the SQL that computes it
     * @param callable $compute  computes the total
     * @param mixed    $counted  an exact total counted elsewhere, or null when there is none
     * @param bool     $live     compute it now instead of reading the cache
     *
     * @return mixed
     */
    public static function resolve(string $key, callable $compute, $counted = null, bool $live = false)
    {
        if ($counted !== null) {
            // Keep the cache current, for when the counted value cannot be used.
            DonationTotalsCache::seed($key, $counted);

            return $counted;
        }

        if ($live) {
            return DonationTotalsCache::recompute($key, $compute);
        }

        return DonationTotalsCache::remember($key, $compute, true);
    }
}
