<?php
namespace Give\DonorProfiles\Repositories;

use Give\ValueObjects\Money;
use Give\Framework\Database\DB;
use Give\Receipt\DonationReceipt;
use InvalidArgumentException;

/**
 * @since 2.10.0
 */
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
		$aggregate = $this->getDonationAggregate( 'count(revenue.id)', $donorId );
		return $aggregate->result;
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
		$aggregate = $this->getDonationAggregate( 'sum(revenue.amount)', $donorId );
		error_log( serialize( $aggregate ) );
		return $this->getAmountWithSeparators( Money::ofMinor( $aggregate->result, give_get_option( 'currency' ) )->getAmount() );
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
		;
		$aggregate = $this->getDonationAggregate( 'avg(revenue.amount)', $donorId );
		return $this->getAmountWithSeparators( Money::ofMinor( $aggregate->result, give_get_option( 'currency' ) )->getAmount() );
	}

	private function getDonationAggregate( $rawAggregate, $donorId ) {
		global $wpdb;
		return DB::get_row(
			DB::prepare(
				"
				SELECT {$rawAggregate} as result
				FROM {$wpdb->give_revenue} as revenue
					INNER JOIN {$wpdb->posts} as posts ON revenue.donation_id = posts.ID
					INNER JOIN {$wpdb->prefix}give_donationmeta as donationmeta ON revenue.donation_id = donationmeta.donation_id
				WHERE donationmeta.meta_key = '_give_payment_donor_id'
					AND donationmeta.meta_value = {$donorId}
					AND posts.post_status IN ( 'publish', 'give_subscription' )
			"
			)
		);
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

		$donations = [];
		foreach ( $payments as $payment ) {
			$donations[] = [
				'id'      => $payment->ID,
				'form'    => $this->getFormInfo( $payment ),
				'payment' => $this->getPaymentInfo( $payment ),
				'donor'   => $this->getDonorInfo( $payment ),
				'receipt' => $this->getReceiptInfo( $payment ),
			];
		}
		return $donations;
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
			$pdfReceiptUrl = html_entity_decode( give_pdf_receipts()->engine->get_pdf_receipt_url( $payment->ID ) );
		}

		$gateways = give_get_payment_gateways();

		return [
			'amount'        => $this->getFormattedAmount( $payment->subtotal, $payment ),
			'currency'      => $payment->currency,
			'fee'           => $this->getFormattedAmount( ( $payment->total - $payment->subtotal ), $payment ),
			'total'         => $this->getFormattedAmount( $payment->total, $payment ),
			'method'        => isset( $gateways[ $payment->gateway ]['checkout_label'] ) ? $gateways[ $payment->gateway ]['checkout_label'] : '',
			'status'        => $this->getFormattedStatus( $payment->status ),
			'date'          => date_i18n( give_date_format( 'checkout' ), strtotime( $payment->date ) ),
			'time'          => date_i18n( 'g:i a', strtotime( $payment->date ) ),
			'mode'          => $payment->get_meta( '_give_payment_mode' ),
			'pdfReceiptUrl' => $pdfReceiptUrl,
			'serialCode'    => give_is_setting_enabled( give_get_option( 'sequential-ordering_status', 'disabled' ) ) ? Give()->seq_donation_number->get_serial_code( $payment ) : $payment->ID,
		];
	}

	/**
	 * Get array containing dynamic receipt information
	 *
	 * @param Give_Payment $payment
	 * @return array
	 * @since 2.10.0
	 */
	protected function getReceiptInfo( $payment ) {

		$receipt = new DonationReceipt( $payment->ID );

		/**
		 * Fire the action for receipt object.
		 *
		 * @since 2.7.0
		 */
		do_action( 'give_new_receipt', $receipt );

		$receiptArr = [];

		$sectionIndex = 0;
		foreach ( $receipt as $section ) {
			// Continue if section does not have line items.
			if ( ! $section->getLineItems() ) {
				continue;
			}

			if ( 'PDFReceipt' === $section->id ) {
				continue;
			}

			$receiptArr[ $sectionIndex ]['id'] = $section->id;

			if ( $section->label ) {
				$receiptArr[ $sectionIndex ]['label'] = $section->label;
			}

			/* @var LineItem $lineItem */
			foreach ( $section as $lineItem ) {
				// Continue if line item does not have value.
				if ( ! $lineItem->value ) {
					continue;
				}

				// This class is required to highlight total donation amount in receipt.
				$detailRowClass = '';
				if ( DonationReceipt::DONATIONSECTIONID === $section->id ) {
					$detailRowClass = 'totalAmount' === $lineItem->id ? ' total' : '';
				}

				$receiptArr[ $sectionIndex ]['lineItems'][] = [
					'class' => $detailRowClass,
					'icon'  => '', // Todo: get FontAwesome icon name from stored HTML string $lineItem->icon,
					'label' => html_entity_decode( wp_strip_all_tags( $lineItem->label ) ),
					'value' => html_entity_decode( wp_strip_all_tags( $lineItem->value ) ),
				];

			}

			$sectionIndex++;
		}

		return $receiptArr;
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

	protected function getAmountWithSeparators( $amount ) {
		$formatted = give_format_amount(
			$amount,
			[
				'decimal' => false,
			],
		);

		return $formatted;
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
