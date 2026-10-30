<?php

namespace Give\Donations\Storage;

use Give\Donations\Repositories\DonationTableRepository;

/**
 * Catches every donation write that does not go through DonationRepository: Give_Payment::save(),
 * give_insert_payment(), give_update_payment_status(), give_delete_donation(), the importer and
 * every add-on that writes donation meta. These are legacy hooks on purpose; the model hooks
 * never fire for these paths.
 *
 * @since TBD
 */
class SyncPostsWritesToDonationTable
{
    /**
     * @var DonationTableSync
     */
    private $sync;

    /**
     * @since TBD
     */
    public function __construct(DonationTableSync $sync)
    {
        $this->sync = $sync;
    }

    /**
     * save_post_give_payment: fires on wp_insert_post and wp_update_post, before any meta exists
     * on insert. The queue flush at shutdown sees the finished record.
     *
     * @since TBD
     */
    public function onSavePost(int $postId): void
    {
        $this->sync->queue($postId);
    }

    /**
     * added_donation_meta, updated_donation_meta, deleted_donation_meta.
     *
     * @since TBD
     *
     * @param int|int[] $metaId
     */
    public function onMetaChange($metaId, int $donationId): void
    {
        $this->sync->queue($donationId);
    }

    /**
     * delete_post: the post still exists here, so it can be loaded one last time. Deletion is
     * immediate rather than queued, since the record is gone once this request ends.
     *
     * @since TBD
     */
    public function onDeletePost(int $postId): void
    {
        if (get_post_type($postId) !== 'give_payment') {
            return;
        }

        $donation = give()->donations->getById($postId);

        if ($donation) {
            give(DonationTableRepository::class)->delete($donation);
        }
    }
}
