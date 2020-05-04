<?php
namespace Give\Receipt;

use Give\Receipt\DetailGroup\Donation;
use Give\Receipt\DetailGroup\Donor;
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
		Donor::class,
		Donation::class,
	];

	/**
	 * Receipt constructor.
	 *
	 * @param $donationId
	 */
	public function __construct( $donationId ) {
		$this->donationId = $donationId;
	}

	/**
	 * Get receipt.
	 *
	 * @param string $class
	 *
	 * @return DetailGroup
	 */
	public function get( $class ) {
		if ( in_array( $class, $this->detailsGroup, true ) ) {
			return $this->detailsGroup[ $class ];
		}

		$classNames = array_flip( $this->getDetailGroupList() );

		$this->detailsGroup[ $class ] = new $this->detailsGroupList[ $classNames[ $class ] ]( $this->donationId );

		return $this->detailsGroup[ $class ];
	}

	/**
	 * @return String[]
	 */
	public function getDetailGroupList() {
		return $this->detailsGroupList;
	}

	/**
	 * Add details group to receipt.
	 *
	 * @since 2.7.0
	 * @param string $class
	 */
	public function addDetailsGroup( $class ) {
		$this->detailsGroupList[] = $class;
	}
}
