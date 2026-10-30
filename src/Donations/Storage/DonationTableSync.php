<?php

namespace Give\Donations\Storage;

use Give\Donations\Repositories\DonationTableRepository;
use Give\Log\Log;
use Throwable;

/**
 * Keeps give_donations in step with writes that did not go through DonationRepository: legacy
 * payment functions, the importer and add-on meta writes. Those arrive as a burst of post and
 * meta hooks, so the listeners queue the donation ID and the queue is flushed once at shutdown.
 * A flush loads the donations through the model, the same way every screen does, and hands them
 * to DonationTableRepository, so there is only ever one mapping from a donation to its row.
 *
 * This class and the legacy listeners are the temporary half of the design and go away when the
 * posts storage is retired.
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
            // Priority 0: before shutdown handlers that may close the database connection or the object cache.
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

        $this->sync(...$donationIds);
    }

    /**
     * Write the rows for the given donations from their current posts state. Chunked so a large
     * caller, the backfill for one, never loads more than a hundred models at once. A chunk that
     * fails is retried one donation at a time, so one unloadable donation is logged on its own
     * instead of taking the other ninety-nine with it.
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

        foreach (array_chunk($donationIds, self::FLUSH_THRESHOLD) as $chunk) {
            try {
                $this->syncChunk($chunk);
            } catch (Throwable $exception) {
                if (count($chunk) === 1) {
                    Log::error('Failed syncing a donation to the give_donations table', [
                        'donationId' => $chunk[0],
                        'error' => $exception->getMessage(),
                    ]);
                    continue;
                }

                foreach ($chunk as $donationId) {
                    $this->sync($donationId);
                }
            }
        }
    }

    /**
     * @since TBD
     */
    private function syncChunk(array $donationIds): void
    {
        $donations = give()->donations->prepareQuery()->whereIn('ID', $donationIds)->getAll() ?: [];

        // Duplicate meta rows duplicate the joined result; the first row wins, as it does when a
        // single donation is loaded.
        $byId = [];
        foreach ($donations as $donation) {
            $byId[$donation->id] = $byId[$donation->id] ?? $donation;
        }

        if ($byId) {
            give(DonationTableRepository::class)->insert(...array_values($byId));
        }
    }
}
