<?php

namespace Give\Revenue\Listeners;

use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Revenue\Repositories\Revenue;

class DeleteRevenueWhenDonationDeleted
{
    /**
     * @var Revenue
     */
    private $revenueRepository;

    /**
     * DeleteRevenueWhenDonationDeleted constructor.
     *
     * @since 2.9.2
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
     * @since 2.9.2
     * @since 2.9.4 removed $post parameter for < WP 5.5 compatibility
     *
     * @param int $postId
     *
     * @throws DatabaseQueryException
     */
    public function __invoke($postId)
    {
        if ('give_payment' !== get_post_type($postId)) {
            return;
        }

        $this->revenueRepository->deleteByDonationId($postId);
    }
}
