<?php

namespace Give\Revenue\Listeners;

use Give\Donations\Models\Donation;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Revenue\Repositories\Revenue;

/**
 * @unreleased
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
     * @unreleased
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
     * @unreleased
     */
    public function __invoke(Donation $donation)
    {
        if (! $donation->id) {
            return;
        }

        $this->revenueRepository->deleteByDonationId((int) $donation->id);
    }
}
