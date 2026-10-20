<?php

namespace Give\Donations\Storage;

use Give\Donations\Repositories\DonationTableRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Log\Log;
use Throwable;

/**
 * Keeps give_donations in step with writes that did not go through the repository: reads the
 * donation back from posts and meta, decodes it, and upserts the row through
 * DonationTableRepository. The repository itself writes rows from the model; buildRow() here
 * must produce the same row, and the parity test in the storage suite fails when it does not.
 * The daily drift check grades stored rows against this decode in production.
 *
 * Legacy writes arrive as a burst of post and meta hooks, so listeners queue the donation ID and
 * the queue is flushed once at shutdown. This class, the legacy listeners and the decoder are
 * the temporary half of the design and go away when the posts storage is retired.
 *
 * @since TBD
 */
class DonationTableSync
{
    const FLUSH_THRESHOLD = 100;

    /**
     * @var array<int, true>
     */
    private $queued = [];

    /**
     * @var bool
     */
    private $flushScheduled = false;

    /**
     * Whether the table is being written to at all. Posts means the table does not exist or is
     * not trusted, so nothing is written.
     *
     * @since TBD
     */
    public function isEnabled(): bool
    {
        return !DonationStorageFlag::get()->isPosts();
    }

    /**
     * Remember a donation to sync at shutdown. Used by the legacy listeners, which fire many times
     * for one donation.
     *
     * @since TBD
     */
    public function queue(int $donationId): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->queued[$donationId] = true;

        if (count($this->queued) >= self::FLUSH_THRESHOLD) {
            $this->flush();

            return;
        }

        if (!$this->flushScheduled) {
            $this->flushScheduled = true;
            add_action('shutdown', [$this, 'flush'], 0);
        }
    }

    /**
     * Sync every queued donation. Failures are logged, never thrown: a legacy write already
     * happened and the daily drift check exists to catch what this misses.
     *
     * @since TBD
     */
    public function flush(): void
    {
        if (!$this->queued) {
            return;
        }

        $donationIds = array_keys($this->queued);
        $this->queued = [];

        try {
            $this->sync(...$donationIds);
        } catch (Throwable $exception) {
            Log::error('Failed syncing donations to the give_donations table', [
                'donationIds' => $donationIds,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Upsert the given donations from posts storage. Donations that no longer exist as
     * give_payment posts are removed from the table.
     *
     * @since TBD
     */
    public function sync(int ...$donationIds): void
    {
        if (!$donationIds || !$this->isEnabled()) {
            return;
        }

        $donationIds = array_values(array_unique($donationIds));

        foreach ($donationIds as $donationId) {
            unset($this->queued[$donationId]);
        }

        // Chunked so a large caller, the backfill for one, never builds a multi-megabyte
        // statement or holds a hundred thousand meta rows in memory.
        foreach (array_chunk($donationIds, self::FLUSH_THRESHOLD) as $chunk) {
            $rows = $this->buildRows($chunk);

            $missing = array_diff($chunk, array_keys($rows));
            if ($missing) {
                $this->delete(...$missing);
            }

            if ($rows) {
                give(DonationTableRepository::class)->upsertRows(array_values($rows));
            }
        }
    }

    /**
     * @since TBD
     */
    public function delete(int ...$donationIds): void
    {
        give(DonationTableRepository::class)->deleteByIds(...$donationIds);
    }

    /**
     * @since TBD
     *
     * @return array<int, array> rows keyed by donation ID, in column order
     */
    private function buildRows(array $donationIds): array
    {
        global $wpdb;

        $in = implode(',', array_map('intval', $donationIds));

        $posts = DB::get_results(
            "SELECT ID, post_status, post_parent, post_date, post_date_gmt, post_modified, post_modified_gmt
             FROM {$wpdb->posts}
             WHERE post_type = 'give_payment' AND ID IN ({$in})",
            OBJECT_K
        );

        if (!$posts) {
            return [];
        }

        $meta = [];
        $keys = "'" . implode("','", array_map('esc_sql', DonationMetaKeys::toArray())) . "'";
        $metaRows = DB::get_results(
            "SELECT donation_id, meta_key, meta_value
             FROM {$wpdb->prefix}give_donationmeta
             WHERE donation_id IN ({$in}) AND meta_key IN ({$keys})
             ORDER BY meta_id"
        );

        foreach ($metaRows as $metaRow) {
            // First value wins when a key is duplicated, matching what get_metadata() returns.
            $meta[$metaRow->donation_id][$metaRow->meta_key] = $meta[$metaRow->donation_id][$metaRow->meta_key] ?? $metaRow->meta_value;
        }

        $sequentialNumbers = DB::get_results(
            "SELECT payment_id, id FROM {$wpdb->prefix}give_sequential_ordering WHERE payment_id IN ({$in})",
            OBJECT_K
        );

        $baseCurrency = give_get_option('currency', 'USD');
        $rows = [];

        foreach ($posts as $post) {
            $rows[(int)$post->ID] = $this->buildRow(
                $post,
                $meta[$post->ID] ?? [],
                isset($sequentialNumbers[$post->ID]) ? (int)$sequentialNumbers[$post->ID]->id : null,
                $baseCurrency
            );
        }

        return $rows;
    }

    /**
     * @since TBD
     */
    private function buildRow(object $post, array $meta, ?int $sequentialNumber, string $baseCurrency): array
    {
        [$status, $type] = DonationStatusDecoder::decode($post->post_status, (int)$post->post_parent, $meta);

        $currency = $this->text($meta, DonationMetaKeys::CURRENCY) ?: $baseCurrency;
        $exchangeRate = $meta[DonationMetaKeys::EXCHANGE_RATE] ?? '';
        $exchangeRate = is_numeric($exchangeRate) && $exchangeRate > 0 ? (string)$exchangeRate : '1';
        $mode = $meta[DonationMetaKeys::MODE] ?? '';
        $feeAmountRecovered = $this->text($meta, DonationMetaKeys::FEE_AMOUNT_RECOVERED);

        return [
            'id' => (int)$post->ID,
            'donor_id' => (int)($meta[DonationMetaKeys::DONOR_ID] ?? 0),
            'campaign_id' => $this->id($meta, DonationMetaKeys::CAMPAIGN_ID),
            'form_id' => $this->id($meta, DonationMetaKeys::FORM_ID),
            'subscription_id' => $this->id($meta, DonationMetaKeys::SUBSCRIPTION_ID),
            'status' => $status,
            'type' => $type,
            'mode' => DonationMode::isValid($mode) ? $mode : DonationMode::LIVE,
            'gateway_id' => (string)($meta[DonationMetaKeys::GATEWAY] ?? ''),
            'gateway_transaction_id' => $this->text($meta, DonationMetaKeys::GATEWAY_TRANSACTION_ID),
            'amount_total' => $this->minorUnits($meta[DonationMetaKeys::AMOUNT] ?? '0', $currency),
            'fee_amount_recovered' => $feeAmountRecovered === null ? null : $this->minorUnits($feeAmountRecovered, $currency),
            'currency' => $currency,
            'base_currency' => $baseCurrency,
            'exchange_rate' => $exchangeRate,
            'sequential_number' => $sequentialNumber,
            'purchase_key' => $this->text($meta, DonationMetaKeys::PURCHASE_KEY),
            'email' => (string)($meta[DonationMetaKeys::EMAIL] ?? ''),
            'honorific' => $this->text($meta, DonationMetaKeys::HONORIFIC),
            'first_name' => (string)($meta[DonationMetaKeys::FIRST_NAME] ?? ''),
            'last_name' => (string)($meta[DonationMetaKeys::LAST_NAME] ?? ''),
            'phone' => $this->text($meta, DonationMetaKeys::PHONE),
            'company' => $this->text($meta, DonationMetaKeys::COMPANY),
            'billing_address1' => $this->text($meta, DonationMetaKeys::BILLING_ADDRESS1),
            'billing_address2' => $this->text($meta, DonationMetaKeys::BILLING_ADDRESS2),
            'billing_city' => $this->text($meta, DonationMetaKeys::BILLING_CITY),
            'billing_state' => $this->text($meta, DonationMetaKeys::BILLING_STATE),
            'billing_zip' => $this->text($meta, DonationMetaKeys::BILLING_ZIP),
            'billing_country' => $this->text($meta, DonationMetaKeys::BILLING_COUNTRY),
            'anonymous' => (int)!empty($meta[DonationMetaKeys::ANONYMOUS]),
            'created_at' => $this->utc($post->post_date_gmt, $post->post_date),
            'updated_at' => $this->utc($post->post_modified_gmt, $post->post_modified),
        ];
    }

    /**
     * @since TBD
     */
    private function minorUnits(string $decimal, string $currency): int
    {
        return (int)Money::fromDecimal(is_numeric($decimal) ? $decimal : '0', $currency)->formatToMinorAmount();
    }

    /**
     * @since TBD
     */
    private function text(array $meta, string $key): ?string
    {
        $value = $meta[$key] ?? '';

        return $value === '' || $value === null ? null : (string)$value;
    }

    /**
     * @since TBD
     */
    private function id(array $meta, string $key): ?int
    {
        return empty($meta[$key]) ? null : (int)$meta[$key];
    }

    /**
     * @since TBD
     */
    private function utc(string $gmt, string $local): string
    {
        return $gmt && $gmt !== '0000-00-00 00:00:00' ? $gmt : get_gmt_from_date($local);
    }
}
