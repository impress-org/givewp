<?php

namespace Give\Donations\Counters;

use ActionScheduler;
use Throwable;

/**
 * Schedules the donation counters' recounts through Action Scheduler.
 *
 * - The scheduled rebuild: a recurring action every rebuild interval (the
 *   `givewp_donation_counters_rebuild_interval` filter, 3600 seconds by default). The interval is
 *   read on every scheduling decision, and a scheduled action with another interval is replaced.
 *   0 or less turns it off; then the counters rely on the writes, the recount after a write that
 *   could not be counted, and the `wp give donation-counters rebuild` command.
 * - The recount after a dirty write: one single action about a minute later, however many writes
 *   asked for it (debounced), and never two rebuilds at once (the rebuild holds a lock).
 *
 * Temporary: a stopgap until donations read from the give_donations table (SOFT-5015)
 *
 * @since TBD
 *
 * @internal
 */
class DonationCountersSchedule
{
    /**
     * @since TBD
     */
    public const REBUILD_HOOK = 'givewp_donation_counters_rebuild';

    /**
     * @since TBD
     */
    public const RECOUNT_HOOK = 'givewp_donation_counters_recount';

    /**
     * @since TBD
     */
    public const GROUP = 'givewp_donation_counters';

    /**
     * @since TBD
     */
    public const DEFAULT_INTERVAL = 3600;

    /**
     * The shortest interval a filter may set, so a site cannot recount the donations all the time.
     *
     * @since TBD
     */
    public const MIN_INTERVAL = 60;

    /**
     * How long after a dirty write the recount runs, so a burst of writes asks for one.
     *
     * @since TBD
     */
    public const RECOUNT_DELAY = 60;

    /**
     * How often a read checks that the scheduled rebuild matches the interval, in seconds.
     *
     * @since TBD
     */
    public const CHECK_EVERY = 600;

    /**
     * @var bool
     */
    private static $recountRequested = false;

    /**
     * @var bool
     */
    private static $checked = false;

    /**
     * Seconds between scheduled rebuilds, or 0 when they are off.
     *
     * @since TBD
     */
    public static function interval(): int
    {
        /**
         * Filters how often, in seconds, the donation counters are recounted in full in the
         * background to fix any drift. 0 or less turns the scheduled recount off. Values under 60
         * are raised to 60, and a value that is not a number falls back to 3600.
         *
         * Temporary stopgap, do not rely on it: it goes away when donations read from the
         * give_donations table (SOFT-5015).
         *
         * @since TBD
         *
         * @param int $interval
         */
        $interval = apply_filters('givewp_donation_counters_rebuild_interval', self::DEFAULT_INTERVAL);

        if (!is_numeric($interval)) {
            return self::DEFAULT_INTERVAL;
        }

        $interval = (int)$interval;

        if ($interval <= 0) {
            return 0;
        }

        return max(self::MIN_INTERVAL, $interval);
    }

    /**
     * Makes the scheduled rebuild match the interval: schedules it when missing, replaces it when its
     * interval differs, removes it when the interval is 0 or the counters are off. Checked at most
     * once per CHECK_EVERY seconds from reads, unless the counters look overdue.
     *
     * @since TBD
     */
    public static function ensureScheduled(float $builtAt = 0.0, bool $force = false): void
    {
        if (self::$checked && !$force) {
            return;
        }

        $interval = self::interval();
        $overdue = !$builtAt || ($interval > 0 && microtime(true) - $builtAt > $interval + self::CHECK_EVERY);

        if (!$force && !$overdue && get_transient('givewp_donation_counters_schedule_checked')) {
            return;
        }

        self::$checked = true;

        self::whenReady(static function () use ($interval) {
            self::syncRecurringAction(DonationCounters::isEnabled() ? $interval : 0);
            set_transient('givewp_donation_counters_schedule_checked', 1, self::CHECK_EVERY);
        });
    }

    /**
     * Schedules one recount about a minute from now unless one is already waiting.
     *
     * @since TBD
     */
    public static function scheduleRecount(): void
    {
        if (self::$recountRequested || !DonationCounters::isEnabled()) {
            return;
        }

        self::$recountRequested = true;

        self::whenReady(static function () {
            if (as_has_scheduled_action(self::RECOUNT_HOOK, null, self::GROUP)) {
                return;
            }

            as_schedule_single_action(time() + self::RECOUNT_DELAY, self::RECOUNT_HOOK, [], self::GROUP, true);
        });
    }

    /**
     * @since TBD
     */
    private static function syncRecurringAction(int $interval): void
    {
        $actionIds = as_get_scheduled_actions([
            'hook' => self::REBUILD_HOOK,
            'group' => self::GROUP,
            'status' => \ActionScheduler_Store::STATUS_PENDING,
            'per_page' => 5,
        ], 'ids');

        $matches = false;

        foreach ($actionIds as $actionId) {
            $schedule = ActionScheduler::store()->fetch_action($actionId)->get_schedule();

            if (!$matches && $interval > 0 && method_exists($schedule, 'get_recurrence') && (int)$schedule->get_recurrence() === $interval) {
                $matches = true;
                continue;
            }

            ActionScheduler::store()->cancel_action($actionId);
        }

        if (!$matches && $interval > 0) {
            as_schedule_recurring_action(time() + $interval, $interval, self::REBUILD_HOOK, [], self::GROUP);
        }
    }

    /**
     * Runs the callback once Action Scheduler can take actions: now, or when it initializes.
     *
     * @since TBD
     */
    private static function whenReady(callable $callback): void
    {
        $run = static function () use ($callback) {
            try {
                $callback();
            } catch (Throwable $exception) {
                // Scheduling is best effort: a read finds the counters missing or dirty and asks again.
            }
        };

        if (!function_exists('as_schedule_single_action')) {
            return;
        }

        if (did_action('action_scheduler_init')) {
            $run();

            return;
        }

        add_action('action_scheduler_init', $run);
    }

    /**
     * Forgets what this request already scheduled. For tests and long-running processes.
     *
     * @since TBD
     */
    public static function reset(): void
    {
        self::$recountRequested = false;
        self::$checked = false;
    }
}
