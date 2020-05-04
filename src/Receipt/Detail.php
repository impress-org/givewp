<?php
namespace Give\Receipt;

abstract  class Detail {
	protected $donationId;

	/**
	 * DetailGroup constructor.
	 *
	 * @param int $donationId
	 */
	public function __construct( $donationId ) {
		$this->donationId = $donationId;
	}

	/**
	 * Get label.
	 *
	 * @return mixed
	 */
	abstract public function getLabel();

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	abstract public function getValue();


	/**
	 * Return icon which represent detail.
	 *
	 * @return string
	 */
	public function getIcon() {
		return '';
	}
}
