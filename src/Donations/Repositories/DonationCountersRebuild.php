<?php

namespace Give\Donations\Repositories;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Framework\Database\DB;
use Throwable;

/**
 * Recounts every donation counter from the database and fixes any drift, without losing the writes
 * that land while it counts.
 *
 * The counts run inside one consistent snapshot (REPEATABLE READ), which also reads the counters as
 * they were at that moment. Each bucket then gets `count = count + (recount - counter in snapshot)`
 * in one atomic statement. A write after the snapshot changed the counter after it as well, so its
 * delta survives; a write before it is in both numbers. The one unsafe moment is a write whose data
 * is committed but whose counter update is not yet: each request holds a "writing" row for that
 * time, and the rebuild only starts a snapshot in which no such row is held, trying again shortly
 * otherwise. If it never finds one, it applies the result and marks the counters dirty, so they are
 * not used until a later recount.
 *
 * Counting uses the same index-driven queries as the stats endpoint, grouped by post status.
 *
 * @since TBD
 */
class DonationCountersRebuild
{
    /**
     * @since TBD
     */
    public const LOCK = 'give_donation_counters_rebuild_lock';

    /**
     * A rebuild at a few million donations takes minutes; past this its lock is taken over.
     *
     * @since TBD
     */
    public const LOCK_EXPIRES_AFTER = 1800;

    /**
     * A writing row older than this belongs to a request that died, in seconds.
     *
     * @since TBD
     */
    public const WRITING_EXPIRES_AFTER = 300;

    /**
     * @var int
     */
    private $attempts;

    /**
     * @var int
     */
    private $retryDelayMicroseconds;

    /**
     * @var callable|null called inside the snapshot after the counts, for tests of the race
     */
    private $afterCount;

    /**
     * @since TBD
     */
    public function __construct(int $attempts = 50, int $retryDelayMicroseconds = 200000, ?callable $afterCount = null)
    {
        $this->attempts = max(1, $attempts);
        $this->retryDelayMicroseconds = max(0, $retryDelayMicroseconds);
        $this->afterCount = $afterCount;
    }

    /**
     * Runs the recount. Returns what happened, for the log and the command.
     *
     * @since TBD
     *
     * @return array{status: string, seconds: float, attempts: int, changed: array<string, int>}
     */
    public function __invoke(): array
    {
        $startedAt = microtime(true);
        $lock = new DonationTotalsLock(self::LOCK, self::LOCK_EXPIRES_AFTER);

        if (!$lock->acquire()) {
            return ['status' => 'busy', 'seconds' => 0.0, 'attempts' => 0, 'changed' => []];
        }

        try {
            $result = $this->rebuild();
        } finally {
            $lock->release();
        }

        $result['seconds'] = round(microtime(true) - $startedAt, 3);

        update_option(DonationCounters::LAST_REBUILD, [
            'status' => $result['status'],
            'seconds' => $result['seconds'],
            'attempts' => $result['attempts'],
            'changedBuckets' => count($result['changed']),
            'finishedAt' => time(),
        ], false);

        return $result;
    }

    /**
     * @since TBD
     *
     * @return array{status: string, seconds: float, attempts: int, changed: array<string, int>}
     */
    private function rebuild(): array
    {
        $attempt = 0;

        do {
            $attempt++;
            $this->startSnapshot();

            try {
                $exact = !$this->hasLiveWriters();

                if (!$exact && $attempt < $this->attempts) {
                    DB::query('ROLLBACK');
                    usleep($this->retryDelayMicroseconds);
                    continue;
                }

                $before = DonationCounters::read();
                $counts = $this->count();

                if ($this->afterCount) {
                    ($this->afterCount)();
                }

                DB::query('COMMIT');
            } catch (Throwable $exception) {
                DB::query('ROLLBACK');

                throw $exception;
            }

            $changed = $this->apply($before['buckets'], $counts, $before['dirty']);
            $this->removeExpiredWriters();

            if (!$exact) {
                DonationCounters::markDirty();
            }

            return ['status' => $exact ? 'exact' : 'inexact', 'seconds' => 0.0, 'attempts' => $attempt, 'changed' => $changed];
        } while (true);
    }

    /**
     * Every bucket's count from the database: test or live, recurring or one-time, per post status.
     *
     * @since TBD
     *
     * @return array<string, int> bucket option name => count
     */
    public function count(): array
    {
        global $wpdb;

        $posts = $wpdb->posts;
        $meta = DB::prefix('give_donationmeta');
        $mode = DonationMetaKeys::MODE;
        $subscription = DonationMetaKeys::SUBSCRIPTION_ID;
        $test = DonationMode::TEST;

        $all = $this->byStatus("SELECT post_status AS status, COUNT(*) AS count FROM {$posts}
            WHERE post_type = 'give_payment' GROUP BY post_status");

        $testMode = $this->byStatus(DB::prepare("SELECT STRAIGHT_JOIN donation.post_status AS status, COUNT(DISTINCT mode.donation_id) AS count
            FROM {$meta} mode INNER JOIN {$posts} donation ON donation.ID = mode.donation_id
            WHERE mode.meta_key = %s AND mode.meta_value = %s AND donation.post_type = 'give_payment'
            GROUP BY donation.post_status", $mode, $test));

        $recurring = $this->byStatus(DB::prepare("SELECT STRAIGHT_JOIN donation.post_status AS status, COUNT(DISTINCT subscription.donation_id) AS count
            FROM {$meta} subscription INNER JOIN {$posts} donation ON donation.ID = subscription.donation_id
            WHERE subscription.meta_key = %s AND subscription.meta_value >= '1' AND donation.post_type = 'give_payment'
            GROUP BY donation.post_status", $subscription));

        $recurringTestMode = $this->byStatus(DB::prepare("SELECT donation.post_status AS status, COUNT(DISTINCT subscription.donation_id) AS count
            FROM {$meta} subscription
            INNER JOIN {$posts} donation ON donation.ID = subscription.donation_id
            INNER JOIN {$meta} mode ON mode.donation_id = subscription.donation_id AND mode.meta_key = %s AND mode.meta_value = %s
            WHERE subscription.meta_key = %s AND subscription.meta_value >= '1' AND donation.post_type = 'give_payment'
            GROUP BY donation.post_status", $mode, $test, $subscription));

        $buckets = [];

        foreach ($all as $status => $count) {
            $testCount = $testMode[$status] ?? 0;
            $recurringCount = $recurring[$status] ?? 0;
            $recurringTestCount = $recurringTestMode[$status] ?? 0;

            $buckets[DonationCounters::bucket('test', 'recurring', $status)] = $recurringTestCount;
            $buckets[DonationCounters::bucket('test', 'onetime', $status)] = $testCount - $recurringTestCount;
            $buckets[DonationCounters::bucket('live', 'recurring', $status)] = $recurringCount - $recurringTestCount;
            $buckets[DonationCounters::bucket('live', 'onetime', $status)] = $count - $testCount - ($recurringCount - $recurringTestCount);
        }

        return $buckets;
    }

    /**
     * Moves each bucket by the difference between the recount and the counter in the snapshot, and
     * records the dirty generation the snapshot covered and the time.
     *
     * @since TBD
     *
     * @param array<string, int> $before counters in the snapshot
     * @param array<string, int> $counts recount in the snapshot
     *
     * @return array<string, int> the deltas applied
     */
    private function apply(array $before, array $counts, int $dirtyGeneration): array
    {
        global $wpdb;

        $deltas = [];

        foreach (array_keys($before + $counts) as $bucket) {
            $delta = ($counts[$bucket] ?? 0) - ($before[$bucket] ?? 0);

            if ($delta !== 0) {
                $deltas[$bucket] = $delta;
            }
        }

        DonationCounters::add($deltas);

        DB::query(DB::prepare(
            "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')
             ON DUPLICATE KEY UPDATE option_value = GREATEST(CAST(option_value AS SIGNED), %d)",
            DonationCounters::CLEAN,
            (string)$dirtyGeneration,
            $dirtyGeneration
        ));

        $builtAt = (string)microtime(true);

        DB::query(DB::prepare(
            "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')
             ON DUPLICATE KEY UPDATE option_value = %s",
            DonationCounters::BUILT_AT,
            $builtAt,
            $builtAt
        ));

        return $deltas;
    }

    /**
     * Starts a read-only snapshot in which every query sees the database as of one moment.
     *
     * @since TBD
     */
    private function startSnapshot(): void
    {
        global $wpdb;

        // Applies to the next transaction only. It fails inside an open transaction, which a
        // background rebuild never runs in; the snapshot then keeps the session's level
        // (REPEATABLE READ by default), so the failure is silenced.
        $suppressed = $wpdb->suppress_errors(true);
        $wpdb->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        $wpdb->suppress_errors($suppressed);

        DB::query('START TRANSACTION WITH CONSISTENT SNAPSHOT');
    }

    /**
     * Whether a request held a writing row when the snapshot started.
     *
     * @since TBD
     */
    private function hasLiveWriters(): bool
    {
        global $wpdb;

        return (bool)DB::get_var(DB::prepare(
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(option_value AS UNSIGNED) > %d",
            DB::esc_like(DonationCounters::WRITING_PREFIX) . '%',
            time() - self::WRITING_EXPIRES_AFTER
        ));
    }

    /**
     * @since TBD
     */
    private function removeExpiredWriters(): void
    {
        global $wpdb;

        DB::query(DB::prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(option_value AS UNSIGNED) <= %d",
            DB::esc_like(DonationCounters::WRITING_PREFIX) . '%',
            time() - self::WRITING_EXPIRES_AFTER
        ));
    }

    /**
     * @since TBD
     *
     * @return array<string, int> post status => count
     */
    private function byStatus(string $sql): array
    {
        $counts = [];

        foreach (DB::get_results($sql) ?: [] as $row) {
            $counts[(string)$row->status] = (int)$row->count;
        }

        return $counts;
    }
}
