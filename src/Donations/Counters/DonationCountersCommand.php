<?php

namespace Give\Donations\Counters;

use WP_CLI;

/**
 * Rebuilds, shows and checks the donation counters behind the Donations screen's stats and count.
 *
 * Temporary: a stopgap until donations read from the give_donations table (SOFT-5015)
 *
 * @since TBD
 *
 * @internal
 */
class DonationCountersCommand
{
    /**
     * Recounts every donation counter now and fixes any drift.
     *
     * ## EXAMPLES
     *
     *     wp give donation-counters rebuild
     *
     * @since TBD
     */
    public function rebuild(): void
    {
        $result = (new DonationCountersRebuild())();

        if ($result['status'] === 'busy') {
            WP_CLI::error('Another rebuild is running.');
        }

        WP_CLI::success(sprintf(
            '%s rebuild in %.2f s after %d attempt(s); %d bucket(s) corrected.',
            ucfirst($result['status']),
            $result['seconds'],
            $result['attempts'],
            count($result['changed'])
        ));
    }

    /**
     * Shows the counters, whether they are used, and the rebuild schedule.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : table or json
     * ---
     * default: table
     * ---
     *
     * @since TBD
     */
    public function status(array $args, array $assocArgs): void
    {
        $state = DonationCounters::read();
        $rows = [];

        foreach ($state['buckets'] as $bucket => $count) {
            $rows[] = ['bucket' => substr($bucket, strlen(DonationCounters::BUCKET_PREFIX)), 'count' => $count];
        }

        $format = $assocArgs['format'] ?? 'table';

        WP_CLI\Utils\format_items($format, $rows, ['bucket', 'count']);

        // The summary is plain text: keep it out of json, csv and yaml output so it still parses.
        if ('table' !== $format) {
            return;
        }

        WP_CLI::log(sprintf(
            'enabled: %s; built at: %s; dirty: %d; clean: %d; interval: %d s; last rebuild: %s',
            DonationCounters::isEnabled() ? 'yes' : 'no',
            $state['builtAt'] ? gmdate('c', (int)$state['builtAt']) : 'never',
            $state['dirty'],
            $state['clean'],
            DonationCountersSchedule::interval(),
            wp_json_encode(get_option(DonationCounters::LAST_REBUILD))
        ));
    }

    /**
     * Compares every counter with a direct recount. Run it while nothing writes donations.
     *
     * ## EXAMPLES
     *
     *     wp give donation-counters verify
     *
     * @since TBD
     */
    public function verify(): void
    {
        $counters = DonationCounters::read()['buckets'];
        $counts = (new DonationCountersRebuild())->count();
        $mismatches = 0;

        foreach (array_keys($counters + $counts) as $bucket) {
            $counter = $counters[$bucket] ?? 0;
            $count = $counts[$bucket] ?? 0;

            if ($counter !== $count) {
                $mismatches++;
                WP_CLI::warning(sprintf('%s: counter %d, recount %d', $bucket, $counter, $count));
            }
        }

        if ($mismatches) {
            WP_CLI::error(sprintf('%d bucket(s) differ.', $mismatches));
        }

        WP_CLI::success(sprintf('All %d bucket(s) match a direct recount.', count($counts)));
    }
}
