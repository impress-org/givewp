<?php

namespace Give\Donations\Storage;

use Give\Donations\Repositories\DonationTableRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationStatus;
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
                // Nested because Log treats a top-level type key as its own field.
                ['report' => $report]
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

        $rowMismatches = $this->rowMismatches();
        $report = array_merge($report, $rowMismatches);

        $drift = $report['missingFromTable']['count'] || $report['missingFromPosts']['count'];

        foreach ($rowMismatches as $mismatch) {
            $drift = $drift || $mismatch['count'];
        }

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

        $table = DB::get_results("SELECT mode, COUNT(*) AS total FROM {$this->table()} GROUP BY mode");

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

        $currency = $this->currencyExpression();
        $sums = [];

        // Each donation is rounded to minor units before summing, as the sync rounds each row, so
        // sub-minor-unit legacy amounts cannot make the sums disagree while every row agrees.
        $posts = DB::get_results(
            "SELECT {$this->modeExpression('mode')} AS mode,
                    {$currency} AS currency,
                    SUM({$this->minorUnits('total', $currency)}) AS total
             FROM {$wpdb->posts} p
             LEFT JOIN {$this->meta('mode', DonationMetaKeys::MODE)}
             LEFT JOIN {$this->meta('currency', DonationMetaKeys::CURRENCY)}
             LEFT JOIN {$this->meta('total', DonationMetaKeys::AMOUNT)}
             WHERE p.post_type = 'give_payment'
             GROUP BY 1, 2"
        );

        foreach ($posts as $row) {
            $sums["{$row->mode}:{$row->currency}"] = [
                'mode' => $row->mode,
                'currency' => $row->currency,
                'posts' => (int)$row->total,
                'table' => 0,
            ];
        }

        $table = DB::get_results(
            "SELECT mode, currency, SUM(amount_total) AS total FROM {$this->table()} GROUP BY mode, currency"
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
             LEFT JOIN {$this->table()} d ON d.id = p.ID
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
            "FROM {$this->table()} d
             LEFT JOIN {$wpdb->posts} p ON p.ID = d.id AND p.post_type = 'give_payment'
             WHERE p.ID IS NULL",
            'd.id'
        );
    }

    /**
     * Rows present in both storages whose status, type, mode, amount, fee, currency or IDs
     * disagree. The expressions apply the model's hydration rules in SQL (see DonationQueryData),
     * so a row that does not match what the model would read shows up here even when counts and
     * sums match. Dates are left out: a change of site timezone would read as drift.
     *
     * Values read from another table compare as BINARY because long-lived sites can have a posts
     * or meta table with a different collation from give_donations, and mixing them is a query
     * error.
     *
     * @since TBD
     */
    private function rowMismatches(): array
    {
        global $wpdb;

        $validStatuses = "'" . implode("','", array_map('esc_sql', array_diff(DonationStatus::toArray(), ['give_subscription']))) . "'";
        $status = "(BINARY d.status <> BINARY CASE
            WHEN p.post_status = 'give_subscription' THEN 'publish'
            WHEN p.post_status IN ({$validStatuses}) THEN p.post_status
            ELSE 'pending' END)";
        $type = "(d.type <> CASE
            WHEN IFNULL(initial.meta_value, '') NOT IN ('', '0') THEN 'subscription'
            WHEN IFNULL(subscription.meta_value, '') NOT IN ('', '0') THEN 'renewal'
            ELSE 'single' END)";
        $mode = "(BINARY d.mode <> BINARY {$this->modeExpression('mode')})";
        $amount = "(d.amount_total <> {$this->minorUnits('total', 'd.currency')})";
        $fee = "(NOT (d.fee_amount_recovered <=> CASE
            WHEN IFNULL(fee.meta_value, '') IN ('', '0') THEN NULL
            ELSE {$this->minorUnits('fee', 'd.currency')} END))";
        $currency = "(BINARY d.currency <> BINARY {$this->currencyExpression()})";
        $donorId = "(d.donor_id <> CAST(IFNULL(donor.meta_value, '0') AS SIGNED))";
        $campaignId = $this->nullableIdMismatch('d.campaign_id', 'campaign');
        $formId = $this->nullableIdMismatch('d.form_id', 'form');
        $subscriptionId = $this->nullableIdMismatch('d.subscription_id', 'subscription');

        $from = "FROM {$this->table()} d
             INNER JOIN {$wpdb->posts} p ON p.ID = d.id AND p.post_type = 'give_payment'
             LEFT JOIN {$this->meta('subscription', DonationMetaKeys::SUBSCRIPTION_ID)}
             LEFT JOIN {$this->meta('initial', DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION)}
             LEFT JOIN {$this->meta('mode', DonationMetaKeys::MODE)}
             LEFT JOIN {$this->meta('total', DonationMetaKeys::AMOUNT)}
             LEFT JOIN {$this->meta('fee', DonationMetaKeys::FEE_AMOUNT_RECOVERED)}
             LEFT JOIN {$this->meta('currency', DonationMetaKeys::CURRENCY)}
             LEFT JOIN {$this->meta('donor', DonationMetaKeys::DONOR_ID)}
             LEFT JOIN {$this->meta('campaign', DonationMetaKeys::CAMPAIGN_ID)}
             LEFT JOIN {$this->meta('form', DonationMetaKeys::FORM_ID)}";

        $expressions = compact('status', 'type', 'mode', 'amount', 'fee', 'currency', 'donorId', 'campaignId', 'formId', 'subscriptionId');

        $selects = [];

        foreach ($expressions as $field => $expression) {
            $selects[] = "SUM({$expression}) AS {$field}";
        }

        $totals = DB::get_row('SELECT ' . implode(', ', $selects) . " {$from}");

        $result = [];

        foreach ($expressions as $field => $expression) {
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
     * A row stores a missing or zero ID as NULL, as the sync does.
     *
     * @since TBD
     */
    private function nullableIdMismatch(string $column, string $alias): string
    {
        return "(NOT ({$column} <=> NULLIF(CAST({$alias}.meta_value AS SIGNED), 0)))";
    }

    /**
     * The currency of the joined meta alias, falling back to the site currency.
     *
     * @since TBD
     */
    private function currencyExpression(): string
    {
        return DB::prepare("IFNULL(NULLIF(currency.meta_value, ''), %s)", give_get_option('currency', 'USD'));
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
     * Resolved per call so a blog switch on multisite is respected.
     *
     * @since TBD
     */
    private function table(): string
    {
        return DB::prefix(DonationTableRepository::TABLE);
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
     * The legacy decimal amount in the joined meta alias, rounded to minor units of the currency
     * the given expression yields: the same conversion as the sync's Money::fromDecimal().
     *
     * @since TBD
     */
    private function minorUnits(string $amountAlias, string $currencyExpression): string
    {
        $currencies = array_unique(array_merge(
            [give_get_option('currency', 'USD')],
            DB::get_col("SELECT DISTINCT currency FROM {$this->table()}")
        ));

        $cases = '';

        foreach ($currencies as $currency) {
            $cases .= DB::prepare(' WHEN %s THEN %d', $currency, Money::fromDecimal('1', $currency)->formatToMinorAmount());
        }

        return "ROUND(CAST(IFNULL(NULLIF({$amountAlias}.meta_value, ''), '0') AS DECIMAL(20, 10)) * (CASE {$currencyExpression}{$cases} ELSE 100 END))";
    }
}
