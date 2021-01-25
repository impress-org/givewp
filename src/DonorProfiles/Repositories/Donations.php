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
	 * Get average donor revenue
	 *
	 * @param int $donorId
	 * @since 2.10.0
	 *
	 * @return string
	 */
	public function getAverageRevenue( $donorId ) {
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT AVG(revenue.amount) as amount
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
	 * Get all donation ids by donor ID
	 *
	 * @param int $donorId
	 * @since 2.10.0
	 * @return array Donation IDs
	 */
	protected function getDonationIDs( $donorId ) {
		global $wpdb;

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"
                SELECT 
                    donation_id as id
                FROM 
                    {$wpdb->give_revenue} as revenue
                INNER JOIN 
                    {$wpdb->posts} as posts ON revenue.donation_id = posts.ID
                WHERE 
                    posts.post_author = %d
                AND 
                    posts.post_status IN ( 'publish', 'give_subscription' )
				",
				$donorId
			)
		);

		$ids = [];
		if ( $result ) {
			foreach ( $result as $donation ) {
				$ids[] = $donation->id;
			}
		}

		return $ids;
	}



	/**
	 * Get all donations by donor ID
	 *
	 * @param int $donorId
	 * @since 2.10.0
	 * @return array Ddonations
	 */
	public function getDonations( $donorId ) {

		$ids = $this->getDonationIds( $donorId );

		$args = [
			'number'   => -1,
			'post__in' => $ids,
		];

		$query    = new \Give_Payments_Query( $args );
		$payments = $query->get_payments();

		$data = [];
		foreach ( $payments as $payment ) {
			$data[ $payment->ID ] = [
				'form'    => $this->getFormInfo( $payment ),
				'payment' => $this->getPaymentInfo( $payment ),
				'donor'   => $this->getDonorInfo( $payment ),
			];
		}
		return $data;
	}

	/**
	 * Get form info
	 *
	 * @param Give_Payment $payment
	 * @since 2.10.0
	 * @return array Payment form info
	 */
	protected function getFormInfo( $payment ) {
		return [
			'title' => $payment->form_title,
			'id'    => $payment->form_id,
		];
	}

	/**
	 * Get payment info
	 *
	 * @param Give_Payment $payment
	 * @since 2.10.0
	 * @return array Payment info
	 */
	protected function getPaymentInfo( $payment ) {
		return [
			'amount'   => $this->getFormattedAmount( $payment->subtotal, $payment ),
			'currency' => $payment->currency,
			'fee'      => $this->getFormattedAmount( ( $payment->total - $payment->subtotal ), $payment ),
			'total'    => $this->getFormattedAmount( $payment->total, $payment ),
			'method'   => $payment->gateway,
			'status'   => $this->getFormattedStatus( $payment->status ),
			'date'     => date( 'F j, Y', strtotime( $payment->date ) ),
			'time'     => date( 'g:i a', strtotime( $payment->date ) ),
		];
	}

	protected function getFormattedStatus( $status ) {
		$statusMap = [
			'publish' => [
				'color' => '#7AD03A',
				'label' => __( 'Complete', 'give' ),
			],
		];

		return $statusMap[ $status ] ? $statusMap[ $status ] : [
			'color' => '#FFBA00',
			'label' => 'Unknown',
		];
	}

	protected function getformattedAmount( $amount, $payment ) {
		return give_currency_filter(
			give_format_amount(
				$amount,
				[
					'donation_id' => $payment->ID,
				]
			),
			[
				'currency_code'   => $payment->currency,
				'decode_currency' => true,
				'sanitize'        => false,
			]
		);
	}

	/**
	 * Get donor info
	 *
	 * @param Give_Payment $payment
	 * @since 2.10.0
	 * @return array Donor info
	 */
	protected function getDonorInfo( $payment ) {
		return $payment->user_info;
	}
}
