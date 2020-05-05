<?php
namespace Give\Receipt;

use Give\Receipt\DonationDetailsGroup\DonationDetailsGroup;
use Give\Receipt\DonorDetailsGroup\DonorDetailsGroup;
use Sabberworm\CSS\Value\String;

class Receipt {
	public $heading = '';
	public $message = '';
	private $donationId;

	/**
	 * Receipt details groups.
	 *
	 * @var array
	 */
	public $detailsGroup = [];

	/**
	 * @var array
	 */
	private $detailsGroupList = [
		DonorDetailsGroup::class,
		DonationDetailsGroup::class,
	];

	/**
	 * Receipt constructor.
	 *
	 * @param $donationId
	 */
	public function __construct( $donationId ) {
		$this->donationId = $donationId;

		$this->setupReceipt();

	}

	/**
	 * Setup receipt.
	 */
	private function setupReceipt() {
		/**
		 * Filter the details group list.
		 *
		 * Developer can use this filter hook to register there details group.
		 */
		$this->detailsGroupList = apply_filters( 'give_get_receipt_details_group_list', $this->detailsGroupList, $this );
	}

	/**
	 * Get receipt.
	 *
	 * @param string $class
	 *
	 * @return DetailGroup
	 */
	public function get( $class ) {
		if ( ! in_array( $class, $this->detailsGroup, true ) ) {
			$classNames                   = array_flip( $this->getDetailGroupList() );
			$this->detailsGroup[ $class ] = null;

			if ( array_key_exists( $class, $classNames ) ) {
				$this->detailsGroup[ $class ] = new $this->detailsGroupList[ $classNames[ $class ] ]( $this->donationId );
			}
		}

		return $this->detailsGroup[ $class ];
	}

	/**
	 * @return String[]
	 */
	public function getDetailGroupList() {
		return $this->detailsGroupList;
	}
}
