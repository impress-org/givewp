<?php
namespace Give\Tracking\TrackingData;

use Exception;
use Give\Tracking\Contracts\TrackData;
use Give_Donors_Query;

/**
 * Class DonorData
 *
 * Represents donor data.
 *
 * @package Give\Tracking\TrackingData
 *
 * @since 2.10.0
 */
class DonorData implements TrackData {
	/* @var DonationData $donationData */
	private $donationData;

	/**
	 * DonorData constructor.
	 *
	 * @param  DonationData  $donationData
	 */
	public function __construct( DonationData $donationData ) {
		$this->donationData = $donationData;
	}

	/**
	 * @inheritdoc
	 * @return array|void
	 */
	public function get() {
		return [
			'donorCount'               => $this->getDonorCount(),
			'avgDonationAmountByDonor' => $this->getAvgDonationAmountByDonor(),
		];
	}

	/**
	 * Returns donor count which donated greater then zero
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getDonorCount() {
		$donorQuery = new Give_Donors_Query(
			[
				'number'          => -1,
				'count'           => true,
				'donation_amount' => 0,
			]
		);

		return $donorQuery->get_donors();
	}

	/**
	 * Get average donation by donor.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	private function getAvgDonationAmountByDonor() {
		try {
			$amount = $this->donationData->getRevenueTillNow() / $this->getDonorCount();
		} catch ( Exception $e ) {
			$amount = '';
		}

		return $amount;
	}
}
