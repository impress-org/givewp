<?php

declare(strict_types=1);

namespace Give\Donors\Repositories;

use DateTimeInterface;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * Loads the donors list table data for a whole page at once, so the columns do not query once per row.
 *
 * @since TBD
 */
class DonorsDataRepository
{
    /**
     * The latest donation date by donor id. Donors without donations are missing.
     *
     * @since TBD
     *
     * @var array<int, DateTimeInterface>
     */
    private $latestDonationDates = [];

    /**
     * Loads the data of the given donors with one query.
     *
     * @since TBD
     *
     * @param int[] $donorIds
     */
    public static function donors(array $donorIds): DonorsDataRepository
    {
        $self = new self();
        $donorIds = array_values(array_unique(array_map('intval', $donorIds)));

        if (!$donorIds) {
            return $self;
        }

        // Same rules as DonorRepository::getDonorLatestDonationDate(), grouped by donor.
        $rows = DB::table('posts')
            ->select(['meta_value', 'donorId'])
            ->selectRaw('MAX(post_date) AS latestDate')
            ->leftJoin('give_donationmeta', 'ID', 'donation_id')
            ->where('post_type', 'give_payment')
            ->where('meta_key', DonationMetaKeys::DONOR_ID)
            ->whereIn('meta_value', array_map('strval', $donorIds))
            ->whereIn('post_status', ['publish', 'give_subscription'])
            ->groupBy('meta_value')
            ->getAll();

        foreach ($rows ?? [] as $row) {
            if ($row->latestDate) {
                $self->latestDonationDates[(int)$row->donorId] = Temporal::toDateTime($row->latestDate);
            }
        }

        return $self;
    }

    /**
     * The date of the donor's latest donation, or null when the donor has none.
     *
     * @since TBD
     */
    public function getLatestDonationDate(int $donorId): ?DateTimeInterface
    {
        return $this->latestDonationDates[$donorId] ?? null;
    }
}
