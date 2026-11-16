<?php

namespace Give\Donations\Repositories;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Throwable;

/**
 * Counters for the numbers the Donations screen displays: the stat tiles and the list count.
 *
 * Every donation sits in exactly one bucket, named after its mode (test or live), its type
 * (recurring or one-time) and its post status. A write moves a donation from one bucket to
 * another (see SyncDonationCounters), and the stats and the list count are sums of buckets. A full
 * recount runs in the background at most every rebuild interval and fixes any drift (see
 * DonationCountersRebuild).
 *
 * Storage: one options row per bucket, not autoloaded, changed only with
 * `INSERT ... ON DUPLICATE KEY UPDATE option_value = option_value + delta`. The option name is a
 * unique key, so concurrent writers never lose an update, and no row is ever read and written back.
 * The rows are read with one direct query, never through get_option(), so a persistent object cache
 * cannot return an old copy. Only numbers are stored, no donor data.
 *
 * Counters drive displayed numbers only. Which rows a page shows must never depend on them.
 *
 * Two more rows track trust: a dirty generation that grows when a write could not be counted
 * exactly, and the clean generation the last recount covered. Counters are used only when they were
 * built at least once, are not dirty, are enabled, and no bucket is negative.
 *
 * @since TBD
 */
class DonationCounters
{
    /**
     * @since TBD
     */
    public const BUCKET_PREFIX = 'give_donation_count_';

    /**
     * @since TBD
     */
    public const STATE_PREFIX = 'give_donation_counters_';

    /**
     * @since TBD
     */
    public const BUILT_AT = self::STATE_PREFIX . 'built_at';

    /**
     * @since TBD
     */
    public const DIRTY = self::STATE_PREFIX . 'dirty';

    /**
     * @since TBD
     */
    public const CLEAN = self::STATE_PREFIX . 'clean';

    /**
     * @since TBD
     */
    public const LAST_REBUILD = self::STATE_PREFIX . 'last_rebuild';

    /**
     * Prefix of the rows a request holds while one of its donation writes is between its before and
     * after hooks. The rebuild waits for none to be held.
     *
     * @since TBD
     */
    public const WRITING_PREFIX = self::STATE_PREFIX . 'writing_';

    /**
     * @since TBD
     */
    public const MODES = ['live', 'test'];

    /**
     * @since TBD
     */
    public const TYPES = ['onetime', 'recurring'];

    /**
     * Whether the counters are on. Off, the screens use the computed and cached totals.
     *
     * @since TBD
     */
    public static function isEnabled(): bool
    {
        /**
         * Filters whether the Donations screen reads its stat tiles and list count from the donation
         * counters. When false the counters are neither read nor kept up to date, and the screen
         * computes the totals (cached by the donation totals cache).
         *
         * @since TBD
         *
         * @param bool $enabled
         */
        return (bool)apply_filters('givewp_donation_counters_enabled', true);
    }

    /**
     * The option name of a bucket.
     *
     * @since TBD
     */
    public static function bucket(string $mode, string $type, string $status): string
    {
        return self::BUCKET_PREFIX . "{$mode}_{$type}_" . self::sanitizeStatus($status);
    }

    /**
     * Adds deltas to buckets in one atomic statement.
     *
     * @since TBD
     *
     * @param array<string, int> $deltas bucket option name => delta
     */
    public static function add(array $deltas): void
    {
        global $wpdb;

        $deltas = array_filter($deltas);

        if (!$deltas) {
            return;
        }

        // The same row order in every statement, so two writers never wait on each other's rows.
        ksort($deltas);

        $values = [];
        $cases = [];

        foreach ($deltas as $name => $delta) {
            $values[] = DB::prepare("(%s, %s, 'off')", $name, (string)(int)$delta);
            $cases[] = DB::prepare('WHEN %s THEN %d', $name, (int)$delta);
        }

        DB::query(
            "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES " . implode(', ', $values)
            . ' ON DUPLICATE KEY UPDATE option_value = CAST(option_value AS SIGNED) + CASE option_name '
            . implode(' ', $cases) . ' ELSE 0 END'
        );
    }

    /**
     * Records that a change could not be counted exactly. The counters are not used until a recount
     * that started after this, which is scheduled here. The same unseen change can be in the totals
     * cache the screens fall back to, so it is marked outdated too.
     *
     * @since TBD
     */
    public static function markDirty(bool $scheduleRecount = true): void
    {
        global $wpdb;

        try {
            DonationTotalsCache::flush();

            DB::query(DB::prepare(
                "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, '1', 'off')
                 ON DUPLICATE KEY UPDATE option_value = CAST(option_value AS SIGNED) + 1",
                self::DIRTY
            ));

            if ($scheduleRecount) {
                DonationCountersSchedule::scheduleRecount();
            }
        } catch (Throwable $exception) {
            // Nothing else to fall back to: the next rebuild and any later write still correct it.
        }
    }

    /**
     * The buckets and the state, straight from the table.
     *
     * @since TBD
     *
     * @return array{buckets: array<string, int>, builtAt: float, dirty: int, clean: int}
     */
    public static function read(): array
    {
        global $wpdb;

        $rows = DB::get_results(DB::prepare(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
            DB::esc_like('give_donation_count') . '%'
        )) ?: [];

        $state = ['buckets' => [], 'builtAt' => 0.0, 'dirty' => 0, 'clean' => 0];

        foreach ($rows as $row) {
            if (strpos($row->option_name, self::BUCKET_PREFIX) === 0) {
                $state['buckets'][$row->option_name] = (int)$row->option_value;
            } elseif ($row->option_name === self::BUILT_AT) {
                $state['builtAt'] = (float)$row->option_value;
            } elseif ($row->option_name === self::DIRTY) {
                $state['dirty'] = (int)$row->option_value;
            } elseif ($row->option_name === self::CLEAN) {
                $state['clean'] = (int)$row->option_value;
            }
        }

        return $state;
    }

    /**
     * The three stat tiles for a mode, or null when the counters cannot be used.
     *
     * @since TBD
     *
     * @return array{donationsCount: int, oneTimeDonationsCount: int, recurringDonationsCount: int}|null
     */
    public static function statistics(bool $testMode): ?array
    {
        $state = self::usableState();

        if ($state === null) {
            return null;
        }

        $mode = $testMode ? 'test' : 'live';
        $oneTime = self::sum($state['buckets'], $mode, ['onetime'], null);
        $recurring = self::sum($state['buckets'], $mode, ['recurring'], null);

        return [
            'donationsCount' => $oneTime + $recurring,
            'oneTimeDonationsCount' => $oneTime,
            'recurringDonationsCount' => $recurring,
        ];
    }

    /**
     * The number of donations of a mode with one of the statuses (every status but trash when
     * none is given), or null when the counters cannot be used.
     *
     * @since TBD
     *
     * @param string[]|null $statuses
     */
    public static function total(bool $testMode, ?array $statuses = null): ?int
    {
        $state = self::usableState();

        if ($state === null) {
            return null;
        }

        return self::sum($state['buckets'], $testMode ? 'test' : 'live', self::TYPES, $statuses);
    }

    /**
     * The state when the counters may be used. Otherwise null, and a recount is scheduled when
     * they are missing, dirty or wrong.
     *
     * @since TBD
     *
     * @return array{buckets: array<string, int>, builtAt: float, dirty: int, clean: int}|null
     */
    private static function usableState(): ?array
    {
        if (!self::isEnabled()) {
            return null;
        }

        try {
            $state = self::read();
        } catch (Throwable $exception) {
            return null;
        }

        DonationCountersSchedule::ensureScheduled($state['builtAt']);

        if (!$state['builtAt'] || $state['dirty'] > $state['clean']) {
            DonationCountersSchedule::scheduleRecount();

            return null;
        }

        if ($state['buckets'] && min($state['buckets']) < 0) {
            self::markDirty();

            return null;
        }

        return $state;
    }

    /**
     * @since TBD
     *
     * @param array<string, int> $buckets
     * @param string[]           $types
     * @param string[]|null      $statuses null for every status but trash
     */
    private static function sum(array $buckets, string $mode, array $types, ?array $statuses): int
    {
        $wanted = $statuses === null ? null : array_map([self::class, 'sanitizeStatus'], $statuses);
        $total = 0;

        foreach ($buckets as $name => $count) {
            list($bucketMode, $bucketType, $status) = array_pad(explode('_', substr($name, strlen(self::BUCKET_PREFIX)), 3), 3, '');

            if ($bucketMode !== $mode || !in_array($bucketType, $types, true)) {
                continue;
            }

            if ($wanted === null ? $status === DonationStatus::TRASH : !in_array($status, $wanted, true)) {
                continue;
            }

            $total += $count;
        }

        return $total;
    }

    /**
     * A post status as it appears in a bucket name.
     *
     * @since TBD
     */
    private static function sanitizeStatus(string $status): string
    {
        return substr(preg_replace('/[^a-z0-9_\-]/', '', strtolower($status)), 0, 100);
    }
}
