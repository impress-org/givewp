<?php

namespace Give\Receipt;

abstract class DetailGroup {
	public $groupId;
	public $heading = '';
	protected $donationId;

	/**
	 * @var Detail[]
	 */
	public $details;

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
	 * Add detail to group.
	 *
	 * @param string $id
	 * @param Detail $detail
	 *
	 * @since 2.7.0
	 */
	public function addDetail( $id, $detail ) {
		$this->detailsList[ $id ] = $detail;
	}

	/**
	 * Get details list.
	 *
	 * @return array
	 */
	public function getDetailsList() {
		return $this->detailsList;
	}
}
