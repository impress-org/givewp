<?php

namespace Give\Donations\Storage;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Log\Log;

/**
 * Compares the posts storage with the give_donations table and reports where they disagree. It
 * never changes data: its job is to make a missed write path visible before any screen reads
 * from the table.
 *
 * Every comparison is one aggregate query over the join of posts, the table and the few meta
 * rows involved, so a run on a large site is a handful of statements, not a per-donation loop.
 * Per-row mismatches carry up to 50 sample IDs for support.
 *
 * @since TBD
 */
class DonationStorageDriftCheck
{
    const OPTION = 'give_donation_storage_drift';
    const SAMPLE_LIMIT = 50;

    /**
     * Runs the comparison, stores the report, and logs it when anything differs.
     *
     * @since TBD
     */
    public function run(): array
    {
        $report = $this->compare();

        update_option(self::OPTION, $report, false);

        if ($report['drift']) {
            Log::warning('The posts storage and the give_donations table disagree', array_merge(
                ['category' => 'Donation Storage'],
                $report
            ));
        }

        return $report;
    }

    /**
     * @since TBD
     */
    public function lastReport(): ?array
    {
        $report = get_option(self::OPTION);

        return is_array($report) ? $report : null;
    }

    /**
     * @since TBD
     */
    public function compare(): array
    {
        $report = [
            'checkedAt' => current_time('mysql', true),
            'counts' => $this->counts(),
            'sums' => $this->sums(),
            'missingFromTable' => $this->missingFromTable(),
            'missingFromPosts' => $this->missingFromPosts(),
        ];

        $report = array_merge($report, $this->rowMismatches());

        $drift = $report['missingFromTable']['count']
            || $report['missingFromPosts']['count']
            || $report['status']['count']
            || $report['type']['count']
            || $report['mode']['count']
            || $report['amount']['count'];

        foreach ($report['counts'] as $modeCounts) {
            $drift = $drift || $modeCounts['posts'] !== $modeCounts['table'];
        }

        foreach ($report['sums'] as $sum) {
            $drift = $drift || $sum['posts'] !== $sum['table'];
        }

        $report['drift'] = (bool)$drift;

        return $report;
    }

    /**
     * Donations per mode in each storage.
     *
     * @since TBD
     */
    private function counts(): array
    {
        global $wpdb;

        $counts = ['live' => ['posts' => 0, 'table' => 0], 'test' => ['posts' => 0, 'table' => 0]];

        $posts = DB::get_results(
            "SELECT {$this->modeExpression('mode')} AS mode, COUNT(*) AS total
             FROM {$wpdb->posts} p
             LEFT JOIN {$this->meta('mode', DonationMetaKeys::MODE)}
             WHERE p.post_type = 'give_payment'
             GROUP BY 1"
        );

        foreach ($posts as $row) {
            $counts[$row->mode]['posts'] = (int)$row->total;
        }

        $table = DB::get_results("SELECT mode, COUNT(*) AS total FROM {$wpdb->give_donations} GROUP BY mode");

        foreach ($table as $row) {
            $counts[$row->mode]['table'] = (int)$row->total;
        }

        return $counts;
    }

    /**
     * SUM(amount_total) per mode and currency in each storage, in minor units. The legacy sum is
     * converted with the same Money rules the sync uses.
     *
     * @since TBD
     */
    private function sums(): array
    {
        global $wpdb;

        $siteCurrency = give_get_option('currency', 'USD');
        $sums = [];

        $posts = DB::get_results(DB::prepare(
            "SELECT {$this->modeExpression('mode')} AS mode,
                    IFNULL(NULLIF(currency.meta_value, ''), %s) AS currency,
                    SUM(CAST(IFNULL(NULLIF(total.meta_value, ''), '0') AS DECIMAL(20, 10))) AS total
             FROM {$wpdb->posts} p
             LEFT JOIN {$this->meta('mode', DonationMetaKeys::MODE)}
             LEFT JOIN {$this->meta('currency', DonationMetaKeys::CURRENCY)}
             LEFT JOIN {$this->meta('total', DonationMetaKeys::AMOUNT)}
             WHERE p.post_type = 'give_payment'
             GROUP BY 1, 2",
            $siteCurrency
        ));

        foreach ($posts as $row) {
            $sums["{$row->mode}:{$row->currency}"] = [
                'mode' => $row->mode,
                'currency' => $row->currency,
                'posts' => (int)Money::fromDecimal(rtrim(rtrim($row->total, '0'), '.') ?: '0', $row->currency)->formatToMinorAmount(),
                'table' => 0,
            ];
        }

        $table = DB::get_results(
            "SELECT mode, currency, SUM(amount_total) AS total FROM {$wpdb->give_donations} GROUP BY mode, currency"
        );

        foreach ($table as $row) {
            $sums["{$row->mode}:{$row->currency}"] = array_merge(
                $sums["{$row->mode}:{$row->currency}"] ?? ['mode' => $row->mode, 'currency' => $row->currency, 'posts' => 0],
                ['table' => (int)$row->total]
            );
        }

        return array_values($sums);
    }

    /**
     * @since TBD
     */
    private function missingFromTable(): array
    {
        global $wpdb;

        return $this->countAndSample(
            "FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->give_donations} d ON d.id = p.ID
             WHERE p.post_type = 'give_payment' AND d.id IS NULL",
            'p.ID'
        );
    }

    /**
     * @since TBD
     */
    private function missingFromPosts(): array
    {
        global $wpdb;

        return $this->countAndSample(
            "FROM {$wpdb->give_donations} d
             LEFT JOIN {$wpdb->posts} p ON p.ID = d.id AND p.post_type = 'give_payment'
             WHERE p.ID IS NULL",
            'd.id'
        );
    }

    /**
     * Rows present in both storages whose status, type, mode or amount disagree. The status and
     * type expressions are the SQL inverse of DonationStatusDecoder, so a wrong decode shows up
     * here even when counts and sums match.
     *
     * @since TBD
     */
    private function rowMismatches(): array
    {
        global $wpdb;

        $status = "(CASE WHEN d.status = 'publish' AND d.type = 'renewal' THEN 'give_subscription' ELSE d.status END <> p.post_status)";
        $type = "(d.type <> CASE
            WHEN p.post_status = 'give_subscription' THEN 'renewal'
            WHEN IFNULL(subscription.meta_value, '') IN ('', '0') THEN 'single'
            WHEN initial.meta_value = '1' OR p.post_parent = 0 THEN 'subscription'
            ELSE 'renewal' END)";
        $mode = "(d.mode <> {$this->modeExpression('mode')})";
        $amount = "(d.amount_total <> ROUND(CAST(IFNULL(NULLIF(total.meta_value, ''), '0') AS DECIMAL(20, 10)) * {$this->minorUnitsPerMajor()}))";

        $from = "FROM {$wpdb->give_donations} d
             INNER JOIN {$wpdb->posts} p ON p.ID = d.id AND p.post_type = 'give_payment'
             LEFT JOIN {$this->meta('subscription', DonationMetaKeys::SUBSCRIPTION_ID)}
             LEFT JOIN {$this->meta('initial', DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION)}
             LEFT JOIN {$this->meta('mode', DonationMetaKeys::MODE)}
             LEFT JOIN {$this->meta('total', DonationMetaKeys::AMOUNT)}";

        $totals = DB::get_row(
            "SELECT SUM({$status}) AS status, SUM({$type}) AS type, SUM({$mode}) AS mode, SUM({$amount}) AS amount {$from}"
        );

        $result = [];

        foreach (compact('status', 'type', 'mode', 'amount') as $field => $expression) {
            $count = (int)($totals->$field ?? 0);
            $result[$field] = [
                'count' => $count,
                'sampleIds' => $count
                    ? array_map('intval', DB::get_col("SELECT d.id {$from} WHERE {$expression} ORDER BY d.id LIMIT " . self::SAMPLE_LIMIT))
                    : [],
            ];
        }

        return $result;
    }

    /**
     * @since TBD
     */
    private function countAndSample(string $fromAndWhere, string $idColumn): array
    {
        $count = (int)DB::get_var("SELECT COUNT(*) {$fromAndWhere}");

        return [
            'count' => $count,
            'sampleIds' => $count
                ? array_map('intval', DB::get_col("SELECT {$idColumn} {$fromAndWhere} ORDER BY {$idColumn} LIMIT " . self::SAMPLE_LIMIT))
                : [],
        ];
    }

    /**
     * @since TBD
     *
     * A LEFT JOIN clause for one meta key, aliased, matching the donation row p. Joins the lowest
     * meta_id only: legacy add_meta() is not unique, and get_metadata() and the sync both take
     * the first row, so a duplicated key must not count as drift or double a sum.
     */
    private function meta(string $alias, string $key): string
    {
        global $wpdb;

        $table = $wpdb->prefix . 'give_donationmeta';

        return DB::prepare(
            "{$table} {$alias} ON {$alias}.meta_id = (
                SELECT MIN(meta_id) FROM {$table} WHERE donation_id = p.ID AND meta_key = %s
            )",
            $key
        );
    }

    /**
     * @since TBD
     *
     * The mode the sync would store for the joined meta alias: live unless the meta says test.
     */
    private function modeExpression(string $alias): string
    {
        return "CASE WHEN {$alias}.meta_value IN ('live', 'test') THEN {$alias}.meta_value ELSE 'live' END";
    }

    /**
     * @since TBD
     *
     * A CASE over every currency in the table giving its minor units per major unit, so the
     * amount comparison uses the same conversion as the sync's Money::fromDecimal().
     */
    private function minorUnitsPerMajor(): string
    {
        global $wpdb;

        $cases = '';

        foreach (DB::get_col("SELECT DISTINCT currency FROM {$wpdb->give_donations}") as $currency) {
            $cases .= DB::prepare(' WHEN %s THEN %d', $currency, Money::fromDecimal('1', $currency)->formatToMinorAmount());
        }

        return $cases ? "(CASE d.currency{$cases} ELSE 100 END)" : '100';
    }
}
