<?php

namespace Give\Receipt;

/**
 * Class DetailGroup
 *
 * This class represent receipt detail group as object and you can add ass many as you want detail item.
 *
 * @since 2.7.0
 * @package Give\Receipt
 */
abstract class DetailGroup {
	/**
	 * Group heading.
	 *
	 * @since 2.7.0
	 * @var string $heading
	 */
	public $heading = '';

	/**
	 * Donation Id.
	 *
	 * since 2.7.0
	 *
	 * @var int $donationId
	 */
	protected $donationId;

	/**
	 * Array of detail item class names.
	 *
	 * @since 2.7.0
	 * @var Detail[]
	 */
	protected $detailsList = [];

	/**
	 * Array of detail item whose object will be create by custom logic.
	 *
	 * @since 2.7.0
	 * @var array
	 */
	protected $detailItemObject = [];

	/**
	 * DetailGroup constructor.
	 *
	 * @since 2.7.0
	 * @param int $donationId
	 */
	public function __construct( $donationId ) {
		$this->donationId = $donationId;
	}

	/**
	 * Get detail item object.
	 *
	 * @param string $class
	 *
	 * @return Detail
	 * @since 2.7.0
	 */
	public function getDetailItemObject( $class ) {
		if ( array_key_exists( $class, $this->detailItemObject ) ) {
			return call_user_func( $this->detailItemObject[ $class ], $this->donationId );
		}

		$classNames           = $this->getDetailItemList();
		$detailGroupClassName = $classNames[ array_search( $class, $classNames, true ) ];

		return new $detailGroupClassName( $this->donationId );
	}

	/**
	 * Get detail item list.
	 *
	 * @since 2.7.0
	 * @return array
	 */
	public function getDetailItemList() {
		return $this->detailsList;
	}

	/**
	 * Get donation id.
	 *
	 * @since 2.7.0
	 * @return int
	 */
	public function getDonationId() {
		return $this->donationId;
	}

	/**
	 * Add detail item.
	 *
	 * @since 2.7.0
	 * @param string $className
	 */
	public function addDetailItem( $className ) {
		$this->detailsList[] = $className;
	}

	/**
	 * Remove detail item.
	 *
	 * @since 2.7.0
	 * @param string $className
	 */
	public function removeDetailItem( $className ) {
		if ( in_array( $className, $this->detailsList, true ) ) {
			unset( $this->detailsList[ array_search( $className, $this->detailsList, true ) ] );
		}
	}

	/**
	 * Add detail item object creator callback.
	 *
	 * @since 2.7.0
	 * @param string $className
	 */
	public function addDetailItemObjectCreationHandler( $className ) {
		$this->detailItemObject[] = $className;
	}

	/**
	 * Remove detail item object creator callback.
	 *
	 * @since 2.7.0
	 * @param string $className
	 */
	public function removeDetailItemObjectCreationHandler( $className ) {
		if ( in_array( $className, $this->detailItemObject, true ) ) {
			unset( $this->detailItemObject[ array_search( $className, $this->detailItemObject, true ) ] );
		}
	}

	/**
	 * Return whether or not to render details group.
	 *
	 * Each detail item need specific context/condition to render. For example recurring related detail will on render if donation is a subscription.
	 * We can use this function in receipt of form template to prevent unnecessary detail group output.
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public function canShow() {
		return true;
	}
}
