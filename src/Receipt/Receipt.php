<?php
namespace Give\Receipt;

use Give\Receipt\DonationDetailsGroup\DonationDetailsGroup;
use Give\Receipt\AdditionalDetailsGroup\AdditionalDetailsGroup;
use Give\Receipt\DonorDetailsGroup\DonorDetailsGroup;

/**
 * Class Receipt
 *
 * This class represent receipt as object.
 * Receipt can have multiple detail group and detail group can has multiple detail item.
 * You can add your own logic to render receipt because it does not return any UI item.
 *
 * @since 2.7.0
 * @package Give\Receipt
 */
class Receipt {
	/**
	 * Receipt Heading.
	 *
	 * @since 2.7.0
	 * @var string $heading
	 */
	public $heading = '';

	/**
	 * Receipt message.
	 *
	 * @since 2.7.0
	 * @var string $message
	 */
	public $message = '';

	/**
	 * Donation id.
	 *
	 * @since 2.7.0
	 * @var int $donationId
	 */
	private $donationId;

	/**
	 * Receipt details group class names.
	 *
	 * @since 2.7.0
	 * @var array
	 */
	private $detailsGroupList = [
		DonorDetailsGroup::class,
		DonationDetailsGroup::class,
		AdditionalDetailsGroup::class,
	];

	/**
	 * Receipt constructor.
	 *
	 * @since 2.7.0
	 * @param $donationId
	 */
	public function __construct( $donationId ) {
		$this->donationId = $donationId;

		$this->setupReceipt();

	}

	/**
	 * Setup receipt.
	 *
	 * @since 2.7.0
	 */
	private function setupReceipt() {
		/**
		 * Filter the detail group class names.
		 *
		 * Developer can use this filter hook to register there detail group.
		 */
		$this->detailsGroupList = apply_filters( 'give_receipt_register_details_group', $this->detailsGroupList, $this->donationId );
	}

	/**
	 * Get detail group object.
	 *
	 * @param string $class
	 *
	 * @return DetailGroup|null
	 * @since 2.7.0
	 */
	public function getDetailGroupObject( $class ) {
		/**
		 * Use this filter to handle object initialization for your detail group class.
		 *
		 * @since 2.7.0
		 */
		$object = apply_filters( 'give_receipt_create_detail_group_object', null, $class, $this->donationId );

		if ( $object instanceof DetailGroup ) {
			return $object;
		}

		$classNames           = $this->getDetailGroupList();
		$detailGroupClassName = $classNames[ array_search( $class, $classNames, true ) ];

		if ( $detailGroupClassName ) {
			$object = new $detailGroupClassName( $this->donationId );
		}

		return $object;
	}

	/**
	 * Return list of detail group class names.
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	public function getDetailGroupList() {
		return $this->detailsGroupList;
	}
}
