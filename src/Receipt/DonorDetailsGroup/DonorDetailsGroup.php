<?php
namespace Give\Receipt\DonorDetailsGroup;

use Give\Receipt\DetailGroup;
use Give\Receipt\DonorDetailsGroup\Details\BillingAddress;
use Give\Receipt\DonorDetailsGroup\Details\Email;
use Give\Receipt\DonorDetailsGroup\Details\Name;

class DonorDetailsGroup extends DetailGroup {
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
