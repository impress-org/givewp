<?php

namespace Give\Receipt;

use InvalidArgumentException;

/**
 * Class Section
 *
 * This class represent receipt detail group as object and you can add ass many as you want detail item.
 *
 * @since 2.7.0
 * @package Give\Receipt
 */
class Section {
	/**
	 * Section heading.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $label = '';

	/**
	 * Section ID.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $id = '';

	/**
	 * Array of detail item class names.
	 *
	 * @since 2.7.0
	 * @var LineItem[]
	 */
	protected $lineItems = [];

	/**
	 * Section constructor.
	 *
	 * @param string $id
	 * @param string $label
	 */
	public function __construct( $id, $label ) {
		$this->id    = $id;
		$this->label = $label;
	}

	/**
	 * Add detail group.
	 *
	 * @param  array $listItem
	 *
	 * @since 2.7.0
	 */
	public function addLineItem( $listItem ) {
		$this->validateLineItem( $listItem );

		$icon = isset( $listItem['icon'] ) ? $listItem['icon'] : '';

		$listItem = new LineItem( $listItem['id'], $listItem['label'], $listItem['value'], $icon );

		$this->lineItems[ $listItem->id ] = $listItem;
	}

	/**
	 * Remove line item.
	 *
	 * @param  string $lineItemId
	 *
	 * @since 2.7.0
	 */
	public function removeLineItem( $lineItemId ) {
		if ( in_array( $lineItemId, $this->lineItems, true ) ) {
			unset( $this->lineItems[ $lineItemId ] );
		}
	}

	/**
	 * Validate line item.
	 *
	 * @param  array $array
	 *
	 * @since 2.7.0
	 */
	protected function validateLineItem( $array ) {
		$required = [ 'id', 'label', 'value' ];
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( __( 'Invalid receipt section line item. Please provide valid line item id, label, and value.', 'give' ) );
		}
	}
}
