<?php
namespace Give\Receipt\DonorDetailsGroup;

use Give\Receipt\DetailGroup;
use Give\Receipt\DonorDetailsGroup\Details\BillingAddress;
use Give\Receipt\DonorDetailsGroup\Details\Email;
use Give\Receipt\DonorDetailsGroup\Details\Name;

/**
 * Class DonorDetailsGroup
 *
 * @since 2.7.0
 * @package Give\Receipt\DonorDetailsGroup
 */
class DonorDetailsGroup extends DetailGroup {
	/**
	 * @inheritDoc
	 */
	protected $detailsList = [
		Name::class,
		Email::class,
		BillingAddress::class,
	];

	/**
	 * Donor constructor.
	 *
	 * @since 2.7.0
	 * @param $donationId
	 */
	public function __construct( $donationId ) {
		parent::__construct( $donationId );

		$this->heading = esc_html__( 'Donation Details', 'give' );
	}
}
