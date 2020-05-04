<?php
namespace Give\Receipt\DetailGroup;

use Give\Receipt\Detail\Donor\BillingAddress;
use Give\Receipt\Detail\Donor\Name;
use Give\Receipt\Detail\Donor\Email;
use Give\Receipt\DetailGroup;

class Donor extends DetailGroup {
	public $groupId = 'donorDetails';

	protected $detailsList = [
		Name::class,
		Email::class,
		BillingAddress::class,
	];

	/**
	 * Donor constructor.
	 *
	 * @param $donationId
	 */
	public function __construct( $donationId ) {
		parent::__construct( $donationId );

		$this->heading = __( 'Donation Details', 'give' );
	}
}
