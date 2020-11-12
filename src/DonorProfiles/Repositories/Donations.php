<?php
namespace Give\DonorProfiles\Repositories;

use Give\ValueObjects\Money;
use InvalidArgumentException;

class Donations {
	/**
	 * Get donations count for donor
	 *
	 * @param int $donorId
	 * @since 2.10.0
	 *
	 * @return int
	 */
	public function getDonationCount( $donorId ) {
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT count(revenue.id) as count
				FROM {$wpdb->give_revenue} as revenue
				INNER JOIN {$wpdb->posts} as posts
				ON revenue.donation_id = posts.ID
				WHERE posts.post_author = %d
				AND posts.post_status IN ( 'publish', 'give_subscription' )
				",
				$donorId
			)
		);

		if ( ! $result ) {
			return 0;
		}

		return $result->count;
	}


	/**
	 * Get donor revenue
	 *
	 * @param int $donorId
	 * @since 2.10.0
	 *
	 * @return string
	 */
	public function getRevenue( $donorId ) {
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT SUM(revenue.amount) as amount
				FROM {$wpdb->give_revenue} as revenue
				INNER JOIN {$wpdb->posts} as posts
				ON revenue.donation_id = posts.ID
				WHERE posts.post_author = %d
				AND posts.post_status IN ( 'publish', 'give_subscription' )
				",
				$donorId
			)
		);

		if ( ! $result ) {
			return 0;
		}

		return Money::ofMinor( $result->amount, give_get_option( 'currency' ) )->getAmount();
	}



	/**
	 * Get all donations by donor ID
	 *
	 * @param int $donorId
	 * @since 2.10.0
	 * @return array Donation IDs
	 */
	public function getDonations( $donorId ) {
		global $wpdb;

		$data = [];

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT donation_id as id
				FROM {$wpdb->give_revenue} as revenue
				INNER JOIN {$wpdb->posts} as posts
				ON revenue.donation_id = posts.ID
				WHERE posts.post_author = %d
				AND posts.post_status IN ( 'publish', 'give_subscription' )
				",
				$donorId
			)
		);

		if ( $result ) {
			foreach ( $result as $donation ) {
				$data[] = $donation->id;
			}
		}

		return $data;
	}
}
