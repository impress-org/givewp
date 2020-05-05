<?php

namespace Give\Receipt;

abstract class DetailGroup {
	public $groupId;
	public $heading = '';
	public $donationId;

	/**
	 * @var Detail[]
	 */
	public $details = [];

	/**
	 * Array of class names.
	 *
	 * @var array
	 */
	protected $detailsList;

	/**
	 * DetailGroup constructor.
	 *
	 * @param int $donationId
	 */
	public function __construct( $donationId ) {
		$this->donationId = $donationId;

		$this->setupDetailsGroup();
	}

	/**
	 * Setup receipt.
	 */
	private function setupDetailsGroup() {
		/**
		 * Filter the details group list items.
		 *
		 * Developer can use this filter hook to register there details group list item.
		 */
		$this->detailsList = apply_filters( 'give_receipt_register_detail_item', $this->detailsList, $this );
	}

	/**
	 * Get details
	 *
	 * @param string $class
	 * @return Detail
	 */
	public function get( $class ) {
		if ( in_array( $class, $this->details, true ) ) {
			return $this->details[ $class ];
		}

		$classNames              = array_flip( $this->getDetailsList() );
		$this->details[ $class ] = new $this->detailsList[ $classNames[ $class ] ]( $this->donationId );

		return $this->details[ $class ];
	}

	/**
	 * Get details list.
	 *
	 * @return array
	 */
	public function getDetailsList() {
		return $this->detailsList;
	}

	/**
	 * Return whether or not to render details group.
	 *
	 * @return bool
	 */
	public function canShow() {
		return true;
	}
}
