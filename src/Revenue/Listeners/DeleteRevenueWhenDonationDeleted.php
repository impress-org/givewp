<?php

namespace Give\Revenue\Listeners;

use Give\Donations\Models\Donation;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Revenue\Repositories\Revenue;

/**
 * @since 4.14.0
 */
class DeleteRevenueWhenDonationDeleted
{
    /**
     * @var Revenue
     */
    private $revenueRepository;

    /**
     * DeleteRevenueWhenDonationDeleted constructor.
     *
     * @since 4.14.0
     *
     * @param Revenue $revenueRepository
     */
    public function __construct(Revenue $revenueRepository)
    {
        $this->revenueRepository = $revenueRepository;
    }

    /**
     * Deletes the revenue associated with a donation when a donation is deleted
     *
     * @since 4.14.0
     */
    public function __invoke(Donation $donation)
    {
        if (! $donation->id) {
            return;
        }

        $this->revenueRepository->deleteByDonationId((int) $donation->id);
    }
}
