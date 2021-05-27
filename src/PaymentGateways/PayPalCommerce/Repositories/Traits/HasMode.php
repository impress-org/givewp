<?php
namespace Give\PaymentGateways\PayPalCommerce\Repositories\Traits;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

trait HasMode {
	/**
	 * The current working mode: live or sandbox
	 *
	 * @since 2.9.0
	 *
	 * @var string
	 */
	protected $mode;

	/**
	 * Sets the mode for the repository for handling operations
	 *
	 * @since 2.9.0
	 *
	 * @param $mode
	 *
	 * @return $this
	 */
	public function setMode( $mode ) {
		if ( ! in_array( $mode, [ 'live', 'sandbox' ], true ) ) {
			throw new InvalidArgumentException( "Must be either 'live' or 'sandbox', received: $mode" );
		}

		$this->mode = $mode;

		return $this;
	}
}
