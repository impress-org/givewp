<?php

namespace Give\Revenue\Listeners;

use Give\Framework\Database\DB;
use Give\Revenue\Repositories\Revenue;
use WP_Post;

class DeleteRevenueWhenDonationDeleted {
	/**
	 * @var Revenue
	 */
	private $revenueRepository;

	/**
	 * DeleteRevenueWhenDonationDeleted constructor.
	 *
	 * @since 2.9.2
	 */
	public function __construct( Revenue $revenueRepository ) {
		$this->revenueRepository = $revenueRepository;
	}

	/**
	 * Deletes the revenue associated with a donation when a donation is deleted
	 *
	 * @since 2.9.2
	 *
	 * @param int     $postId
	 * @param WP_Post $post
	 */
	public function __invoke( $postId, WP_Post $post ) {
		if ( $post->post_type !== 'give_payment' ) {
			return;
		}

		$this->revenueRepository->deleteByDonationId( $postId );
	}
}
