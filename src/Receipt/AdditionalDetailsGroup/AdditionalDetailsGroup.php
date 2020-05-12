<?php
namespace Give\Receipt\AdditionalDetailsGroup;

use Give\Receipt\DetailGroup;

/**
 * Class AdditionalDetailsGroup
 *
 * @since 2.7.0
 * @package Give\Receipt\AdditionalDetailsGroup
 */
class AdditionalDetailsGroup extends DetailGroup {
	/**
	 * Addition information constructor.
	 *
	 * @since 2.7.0
	 * @param $donationId
	 */
	public function __construct( $donationId ) {
		parent::__construct( $donationId );

		$this->heading = esc_html__( 'Additional Information', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function canShow() {
		return (bool) count( $this->detailsList );
	}
}
