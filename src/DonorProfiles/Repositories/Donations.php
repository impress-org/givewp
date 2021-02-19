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

		$pdfReceiptUrl = '';
		if ( class_exists( 'Give_PDF_Receipts' ) ) {
			$pdfReceiptUrl = give_pdf_receipts()->engine->get_pdf_receipt_url( $payment->ID );
		}

		$gateways = give_get_payment_gateways();

		return [
			'amount'        => $this->getFormattedAmount( $payment->subtotal, $payment ),
			'currency'      => $payment->currency,
			'fee'           => $this->getFormattedAmount( ( $payment->total - $payment->subtotal ), $payment ),
			'total'         => $this->getFormattedAmount( $payment->total, $payment ),
			'method'        => $gateways[ $payment->gateway ]['checkout_label'],
			'status'        => $this->getFormattedStatus( $payment->status ),
			'date'          => date_i18n( give_date_format( 'checkout' ), strtotime( $payment->date ) ),
			'time'          => date_i18n( 'g:i a', strtotime( $payment->date ) ),
			'mode'          => $payment->get_meta( '_give_payment_mode' ),
			'pdfReceiptUrl' => $pdfReceiptUrl,
		];
	}

	/**
	 * Get formatted status object (used for rendering status correctly in Donor Profile)
	 *
	 * @param string $status
	 * @since 2.10.0
	 * @return array Formatted status object (with color and label)
	 */
	protected function getFormattedStatus( $status ) {
		$statusMap = [
			'publish' => [
				'color' => '#7AD03A',
				'label' => esc_html__( 'Complete', 'give' ),
			],
		];

		return isset( $statusMap[ $status ] ) ? $statusMap[ $status ] : [
			'color' => '#FFBA00',
			'label' => esc_html__( 'Unknown', 'give' ),
		];
	}

	/**
	 * Get formatted payment amount
	 *
	 * @param float $amount
	 * @param Give_Payment $payment
	 * @since 2.10.0
	 * @return string Formatted payment amount (with correct decimals and currency symbol)
	 */
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
