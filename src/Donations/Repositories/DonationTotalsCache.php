<?php

namespace Give\Donations\Repositories;

/**
 * Remembers totals computed over the donations (list counts and stats) until a donation changes.
 *
 * At a million donations a total reads one meta row per donation and takes tens of seconds, while
 * the list screens ask for the same totals on every visit. Each total is stored under the SQL that
 * computes it, next to a version. Any write to a donation or its meta changes the version, so the
 * next request computes every total again from the database.
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

        $value = $callback();

        // Read again: a donation may have changed while the value was computed.
        $current = self::read();

        if ($current['version'] !== $cache['version']) {
            return $value;
        }

        $current['values'][$hash] = $value;

        if (count($current['values']) > self::MAX_ENTRIES) {
            $current['values'] = array_slice($current['values'], -self::MAX_ENTRIES, null, true);
        }

        update_option(self::OPTION, $current, false);

        return $value;
    }

    /**
     * Drops every cached total. Hooked to donation writes.
     *
     * @since TBD
     */
    public static function flush(): void
    {
        $cache = self::read();

        // Nothing was cached since this request last flushed, so there is nothing to drop.
        if (self::$flushedVersion !== null && self::$flushedVersion === $cache['version'] && !$cache['values']) {
            return;
        }

        $version = uniqid('', true);
        self::$flushedVersion = $version;

        update_option(self::OPTION, ['version' => $version, 'values' => []], false);
    }

    /**
     * @since TBD
     *
     * @return array{version: string, values: array}
     */
    private static function read(): array
    {
        $cache = get_option(self::OPTION);

        if (!is_array($cache) || !isset($cache['version'], $cache['values']) || !is_array($cache['values'])) {
            return ['version' => '', 'values' => []];
        }

        return $cache;
    }
}
