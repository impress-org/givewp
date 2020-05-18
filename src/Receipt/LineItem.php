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
class LineItem {
	/**
	 * Label.
	 *
	 * @since 2.7.0
	 * @var int $donationId
	 */
	public $label;

	/**
	 * Value.
	 *
	 * @since 2.7.0
	 * @var int $donationId
	 */
	public $value;

	/**
	 * Icon.
	 *
	 * @since 2.7.0
	 * @var int $donationId
	 */
	public $icon;

	/**
	 * LineItem constructor.
	 *
	 * @param string $label
	 * @param string $value
	 * @param  string $icon
	 */
	public function __construct( $label, $value, $icon = '' ) {
		$this->label = $label;
		$this->value = $value;
		$this->icon  = $icon;
	}
}
