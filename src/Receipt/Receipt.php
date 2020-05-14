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
		$classNames           = $this->detailsGroupList;
		$detailGroupClassName = $classNames[ array_search( $class, $classNames, true ) ];

		$object = new $detailGroupClassName( $this->donationId );

		/**
		 * fire the action for receipt detail group.
		 *
		 * @since 2.7.0
		 */
		 do_action( 'give_new_receipt_detail_group', $object );

		 return  $object;
	}

	/**
	 * Add detail group.
	 *
	 * @since 2.7.0
	 * @param string $className
	 */
	public function addDetailGroup( $className ) {
		$this->detailsGroupList[] = $className;
	}

	/**
	 * Remove detail group.
	 *
	 * @since 2.7.0
	 * @param string $className
	 */
	public function removeDetailGroup( $className ) {
		if ( in_array( $className, $this->detailsGroupList, true ) ) {
			unset( $this->detailsGroupList[ array_search( $className, $this->detailsGroupList, true ) ] );
		}
	}
}
