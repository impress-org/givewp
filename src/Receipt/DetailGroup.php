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
	public $donationId;

	/**
	 * Array of detail item class names.
	 *
	 * @since 2.7.0
	 * @var Detail[]
	 */
	protected $detailsList = [];

	/**
	 * DetailGroup constructor.
	 *
	 * @since 2.7.0
	 * @param int $donationId
	 */
	public function __construct( $donationId ) {
		$this->donationId = $donationId;

		$this->setupDetailsGroup();
	}

	/**
	 * Setup receipt.
	 *
	 * @since 2.7.0
	 */
	private function setupDetailsGroup() {
		/**
		 * Filter the detail item class names.
		 *
		 * Developer can use this filter hook to register there detail item.
		 */
		$this->detailsList = apply_filters( 'give_receipt_register_detail_item', $this->detailsList, $this );
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
		/**
		 * Use this filter to handle object initialization for your detail item class.
		 *
		 * @since 2.7.0
		 */
		$object = apply_filters( 'give_receipt_create_detail_item_object', null, $class, $this );

		if ( ! ( $object instanceof Detail ) ) {
			$classNames           = $this->getDetailsList();
			$detailGroupClassName = $classNames[ array_search( $class, $classNames, true ) ];

			if ( $detailGroupClassName ) {
				$object = new $detailGroupClassName( $this->donationId );
			}
		}

		return $object;
	}

	/**
	 * Get detail item class names.
	 *
	 * @since 2.7.0
	 * @return array
	 */
	public function getDetailsList() {
		return $this->detailsList;
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
