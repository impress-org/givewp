<?php
namespace Give\Donation;

/**
 * Class Donation
 *
 * @package Give\Donation
 */
class Donation {
	private $id;

	/**
	 * Donation constructor.
	 *
	 * @param int $id
	 */
	public function __construct( $id ) {
		$this->id = absint( $id );
	}


	/**
	 * Return true if donation has pending status otherwise false.
	 *
	 * @since 2.7.0
	 * @return bool
	 */
	public function isPending() {
		return 'pending' === get_post_status( $this->id );
	}
}
