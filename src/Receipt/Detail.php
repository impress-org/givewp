<?php
namespace Give\Receipt;

/**
 * Class Detail
 *
 * This class represent receipt detail item as object.
 *
 * @since 2.7.0
 * @package Give\Receipt
 */
abstract  class Detail {
	/**
	 * Donation id.
	 *
	 * @since 2.7.0
	 * @var int $donationId
	 */
	protected $donationId;

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
	 * Get label.
	 *
	 * @since 2.7.0
	 * @return mixed
	 */
	abstract public function getLabel();

	/**
	 * Get value.
	 *
	 * @since 2.7.0
	 * @return mixed
	 */
	abstract public function getValue();

	/**
	 * Return icon HTML which represent detail.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getIcon() {
		return '';
	}
}
