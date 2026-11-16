<?php

namespace Give\Donations\Repositories;

use Give\Framework\Database\DB;

/**
 * Remembers totals computed over the donations (list counts and stats) until a donation changes.
 *
 * At a million donations a total reads one meta row per donation and takes tens of seconds, while
 * the list screens ask for the same totals on every visit. Each total is stored under the SQL that
 * computes it, next to a version. Any write to a donation, its meta, a subscription or a donor
 * changes the version, so the next request computes every total again from the database.
 *
 * Only one request computes a missing total at a time. The others wait for its result instead of
 * running the same slow query next to it, which made each copy slower.
 *
 * All values live in one option that is not autoloaded and holds at most MAX_ENTRIES totals.
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
     * The version that this request already set, so a burst of writes in one request saves once.
     *
     * @var string|null
     */
    private static $flushedVersion;

    /**
     * Returns the cached value for the key, or computes and stores it.
     *
     * @since TBD
     *
     * @param string   $key      what the value depends on, usually the SQL that computes it
     * @param callable $callback computes the value; the result must be serializable
     *
     * @return mixed
     */
    public static function remember(string $key, callable $callback)
    {
        global $wpdb;

        // A prepared LIKE carries a placeholder for "%" that changes on every request.
        $hash = md5($wpdb->remove_placeholder_escape($key));
        $cache = self::read();

        if (array_key_exists($hash, $cache['values'])) {
            return $cache['values'][$hash];
        }

        $lock = new DonationTotalsLock(self::OPTION . '_lock_' . $hash);

        if (!$lock->acquire()) {
            $cache = self::waitForOtherRequest($hash, $lock);

            if (array_key_exists($hash, $cache['values'])) {
                return $cache['values'][$hash];
            }
        }

        try {
            $value = $callback();
            self::store($hash, $value, $cache['version']);
        } finally {
            $lock->release();
        }

        return $value;
    }

    /**
     * Drops every cached total. Hooked to donation, subscription and donor writes.
     *
     * @since TBD
     */
    public static function flush(): void
    {
        $cache = self::read(true);

        // Nothing was cached since this request last flushed, so there is nothing to drop.
        if (self::$flushedVersion !== null && self::$flushedVersion === $cache['version'] && !$cache['values']) {
            return;
        }

        $version = uniqid('', true);
        self::$flushedVersion = $version;

        update_option(self::OPTION, ['version' => $version, 'values' => []], false);
    }

    /**
     * Saves a value, unless a donation changed since the version the computation started from.
     *
     * @since TBD
     *
     * @param mixed $value
     */
    private static function store(string $hash, $value, string $version): void
    {
        $current = self::read(true);

        if ($current['version'] !== $version) {
            return;
        }

        $current['values'][$hash] = $value;

        if (count($current['values']) > self::MAX_ENTRIES) {
            $current['values'] = array_slice($current['values'], -self::MAX_ENTRIES, null, true);
        }

        update_option(self::OPTION, $current, false);
    }

    /**
     * Polls the cache while another request computes the total. Returns the last cache read, which
     * holds the value unless the wait ran out or the other request stopped without saving it.
     *
     * @since TBD
     *
     * @return array{version: string, values: array}
     */
    private static function waitForOtherRequest(string $hash, DonationTotalsLock $lock): array
    {
        $deadline = time() + self::WAIT_SECONDS;

        do {
            sleep(1);
            $cache = self::read(true);
        } while (!array_key_exists($hash, $cache['values']) && $lock->isHeld() && time() < $deadline);

        return $cache;
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
     * @return array{version: string, values: array}
     */
    private static function read(bool $fresh = false): array
    {
        $cache = $fresh ? self::readFromTable() : get_option(self::OPTION);

        if (!is_array($cache) || !isset($cache['version'], $cache['values']) || !is_array($cache['values'])) {
            return ['version' => '', 'values' => []];
        }

        return $cache;
    }
}
