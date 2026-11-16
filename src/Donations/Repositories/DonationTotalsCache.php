<?php

namespace Give\Donations\Repositories;

use Give\Framework\Database\DB;

/**
 * Remembers totals computed over the donations (list counts and stats).
 *
 * At a million donations a total reads one meta row per donation and takes tens of seconds, while
 * the list screens ask for the same totals on every visit. Each total is stored under the SQL that
 * computes it, with the time its computation started. A write to a donation, its meta, a
 * subscription or a donor records the time of the change; a total computed before it is outdated.
 *
 * By default an outdated total is never returned: the next request computes it again, so totals
 * are exact. A caller that only displays the total may allow outdated ones, and then the max age
 * decides (the `givewp_donation_totals_max_age` filter, 60 seconds by default):
 * - 0: as by default.
 * - more: the outdated total is returned at once. Once it is older than the max age, the request
 *   also computes it again after its response is sent, so the next request gets the new value. On a
 *   site that takes donations every second this runs one computation per total per max age,
 *   instead of one on nearly every page view.
 *
 * Only one request computes a total at a time. On a miss the others wait for its result instead of
 * running the same slow query next to it, which made each copy slower.
 *
 * A total that chooses rows (which page holds which donation) must never come from here: an admin
 * has to find every donation, and only the displayed count may lag.
 *
 * All values live in one option that is not autoloaded and holds at most MAX_ENTRIES totals. The
 * change time has its own option, so a write never rewrites the values.
 *
 * @since TBD
 */
class DonationTotalsCache
{
    /**
     * @since TBD
     */
    public const OPTION = 'give_donation_totals_cache';

    /**
     * The time of the last write that changes the totals, as microtime(true).
     *
     * @since TBD
     */
    public const CHANGED_AT_OPTION = 'give_donation_totals_changed_at';

    /**
     * @since TBD
     */
    public const MAX_ENTRIES = 50;

    /**
     * How long a request waits for another one to compute the same total, in seconds.
     *
     * @since TBD
     */
    public const WAIT_SECONDS = 120;

    /**
     * How long an outdated total is served before it is computed again, in seconds.
     *
     * @since TBD
     */
    public const DEFAULT_MAX_AGE = 60;

    /**
     * When this request last recorded a change, as microtime(true).
     *
     * @var float|null
     */
    private static $changeRecordedAt;

    /**
     * Totals this request will compute again after its response, by hash.
     *
     * @var array<string, callable>
     */
    private static $pendingRefreshes = [];

    /**
     * Returns the cached value for the key, or computes and stores it.
     *
     * @since TBD
     *
     * @param string   $key            what the value depends on, usually the SQL that computes it
     * @param callable $callback       computes the value; the result must be serializable
     * @param bool     $allowOutdated  return an outdated value within the max age, for a total that is only displayed
     *
     * @return mixed
     */
    public static function remember(string $key, callable $callback, bool $allowOutdated = false)
    {
        $hash = self::hash($key);
        $maxAge = $allowOutdated ? self::maxAge() : 0;
        $cache = self::read();

        if (self::isServable($cache, $hash, $maxAge)) {
            if (self::isDue($cache, $hash, $maxAge)) {
                return self::refresh($hash, $callback, $cache['values'][$hash]);
            }

            return $cache['values'][$hash];
        }

        $lock = new DonationTotalsLock(self::OPTION . '_lock_' . $hash);

        if (!$lock->acquire()) {
            $cache = self::waitForOtherRequest($hash, $lock, $maxAge);

            if (self::isServable($cache, $hash, $maxAge)) {
                return $cache['values'][$hash];
            }
        }

        try {
            $value = self::compute($hash, $callback);
        } finally {
            $lock->release();
        }

        return $value;
    }

    /**
     * Computes the value now, past the cache, and stores it for the requests that read the cache.
     *
     * @since TBD
     *
     * @return mixed
     */
    public static function recompute(string $key, callable $callback)
    {
        return self::compute(self::hash($key), $callback);
    }

    /**
     * Stores an exact value that came from elsewhere (the donation counters) as computed now, so a
     * request that later falls back to this cache starts from it instead of an older value. Writes
     * only when the stored value is missing, outdated or different.
     *
     * @since TBD
     *
     * @param mixed $value
     */
    public static function seed(string $key, $value): void
    {
        $hash = self::hash($key);
        $cache = self::read();

        if (array_key_exists($hash, $cache['values']) && $cache['values'][$hash] === $value && !self::isOutdated($cache, $hash)) {
            return;
        }

        self::store($hash, $value, microtime(true));
    }

    /**
     * Records that the totals changed. Hooked to donation, subscription and donor writes.
     *
     * @since TBD
     */
    public static function flush(): void
    {
        // A burst of writes in one request records one change, unless a total was stored since.
        if (self::$changeRecordedAt !== null) {
            $computedAt = self::read(true)['computedAt'];

            if (!$computedAt || max($computedAt) < self::$changeRecordedAt) {
                return;
            }
        }

        self::$changeRecordedAt = microtime(true);

        update_option(self::CHANGED_AT_OPTION, (string)self::$changeRecordedAt, false);
    }

    /**
     * Seconds an outdated total is served before it is computed again. 0 never serves one.
     *
     * @since TBD
     */
    public static function maxAge(): int
    {
        /**
         * Filters how long, in seconds, an outdated donation total is served while it is computed
         * again in the background. 0 computes it again on the next request, so totals are exact.
         *
         * @since TBD
         *
         * @param int $maxAge
         */
        return max(0, (int)apply_filters('givewp_donation_totals_max_age', self::DEFAULT_MAX_AGE));
    }

    /**
     * Whether the cache holds a value for the hash that may be returned: any value when outdated
     * values are allowed, else only one computed after the last change.
     *
     * @since TBD
     */
    private static function isServable(array $cache, string $hash, int $maxAge): bool
    {
        if (!array_key_exists($hash, $cache['values'])) {
            return false;
        }

        return $maxAge > 0 || !self::isOutdated($cache, $hash);
    }

    /**
     * Whether an outdated value has been served for longer than the max age.
     *
     * @since TBD
     */
    private static function isDue(array $cache, string $hash, int $maxAge): bool
    {
        return self::isOutdated($cache, $hash) && microtime(true) - $cache['computedAt'][$hash] >= $maxAge;
    }

    /**
     * @since TBD
     */
    private static function isOutdated(array $cache, string $hash): bool
    {
        return ($cache['computedAt'][$hash] ?? 0.0) <= self::changedAt();
    }

    /**
     * Computes a due value again after the response is sent, and returns the old one now. A server
     * that cannot finish the response early computes it now, as on a miss. Another request that is
     * already computing it is left to finish.
     *
     * @since TBD
     *
     * @param mixed $staleValue
     *
     * @return mixed
     */
    private static function refresh(string $hash, callable $callback, $staleValue)
    {
        if (isset(self::$pendingRefreshes[$hash])) {
            return $staleValue;
        }

        if (!self::canFinishResponseEarly()) {
            return self::refreshNow($hash, $callback, $staleValue);
        }

        if (!self::$pendingRefreshes) {
            add_action('shutdown', [self::class, 'runPendingRefreshes'], PHP_INT_MAX);
        }

        self::$pendingRefreshes[$hash] = $callback;

        return $staleValue;
    }

    /**
     * Sends the response, then computes the totals this request found due.
     *
     * @since TBD
     */
    public static function runPendingRefreshes(): void
    {
        $refreshes = self::$pendingRefreshes;

        if (!$refreshes) {
            return;
        }

        self::finishResponse();

        foreach ($refreshes as $hash => $callback) {
            self::refreshNow($hash, $callback, null);
        }

        self::$pendingRefreshes = [];
    }

    /**
     * Computes a due value unless another request is already doing it, or did it meanwhile.
     *
     * @since TBD
     *
     * @param mixed $staleValue returned when nothing was computed
     *
     * @return mixed
     */
    private static function refreshNow(string $hash, callable $callback, $staleValue)
    {
        $lock = new DonationTotalsLock(self::OPTION . '_lock_' . $hash);

        if (!$lock->acquire()) {
            return $staleValue;
        }

        try {
            $cache = self::read(true);

            if (array_key_exists($hash, $cache['values']) && !self::isDue($cache, $hash, self::maxAge())) {
                return $cache['values'][$hash];
            }

            return self::compute($hash, $callback);
        } finally {
            $lock->release();
        }
    }

    /**
     * Runs the computation and stores the value with the time it started, so a write during the
     * computation leaves it outdated.
     *
     * @since TBD
     *
     * @return mixed
     */
    private static function compute(string $hash, callable $callback)
    {
        $startedAt = microtime(true);
        $value = $callback();
        self::store($hash, $value, $startedAt);

        return $value;
    }

    /**
     * @since TBD
     *
     * @param mixed $value
     */
    private static function store(string $hash, $value, float $computedAt): void
    {
        $current = self::read(true);

        unset($current['values'][$hash], $current['computedAt'][$hash]);
        $current['values'][$hash] = $value;
        $current['computedAt'][$hash] = $computedAt;

        if (count($current['values']) > self::MAX_ENTRIES) {
            $current['values'] = array_slice($current['values'], -self::MAX_ENTRIES, null, true);
            $current['computedAt'] = array_intersect_key($current['computedAt'], $current['values']);
        }

        update_option(self::OPTION, $current, false);
    }

    /**
     * Polls the cache while another request computes the total. Returns the last cache read, which
     * holds the value unless the wait ran out or the other request stopped without saving it.
     *
     * @since TBD
     *
     * @return array{values: array, computedAt: array}
     */
    private static function waitForOtherRequest(string $hash, DonationTotalsLock $lock, int $maxAge): array
    {
        $deadline = time() + self::WAIT_SECONDS;

        do {
            sleep(1);
            $cache = self::read(true);
        } while (!self::isServable($cache, $hash, $maxAge) && $lock->isHeld() && time() < $deadline);

        return $cache;
    }

    /**
     * @since TBD
     */
    private static function hash(string $key): string
    {
        global $wpdb;

        // A prepared LIKE carries a placeholder for "%" that changes on every request.
        return md5($wpdb->remove_placeholder_escape($key));
    }

    /**
     * @since TBD
     */
    private static function changedAt(): float
    {
        return (float)get_option(self::CHANGED_AT_OPTION, 0);
    }

    /**
     * Whether the server can send the response and keep running PHP afterwards.
     *
     * @since TBD
     */
    private static function canFinishResponseEarly(): bool
    {
        return function_exists('fastcgi_finish_request') || function_exists('litespeed_finish_request');
    }

    /**
     * @since TBD
     */
    private static function finishResponse(): void
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        }
    }

    /**
     * The cache straight from the table, past the copy WordPress keeps for the request, so a request
     * sees what other requests wrote.
     *
     * @since TBD
     *
     * @return mixed
     */
    private static function readFromTable()
    {
        global $wpdb;

        $value = DB::get_var(DB::prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", self::OPTION));

        return $value === null ? false : maybe_unserialize($value);
    }

    /**
     * @since TBD
     *
     * @param bool $fresh skip the copy WordPress keeps for this request, to see other requests' writes
     *
     * @return array{values: array, computedAt: array}
     */
    private static function read(bool $fresh = false): array
    {
        $cache = $fresh ? self::readFromTable() : get_option(self::OPTION);

        if (!is_array($cache) || !isset($cache['values'], $cache['computedAt']) || !is_array($cache['values']) || !is_array($cache['computedAt'])) {
            return ['values' => [], 'computedAt' => []];
        }

        return $cache;
    }
}
