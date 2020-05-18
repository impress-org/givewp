<?php

namespace Give\Receipt;

use InvalidArgumentException;
use Iterator;

/**
 * Class Section
 *
 * This class represent receipt detail group as object and you can add ass many as you want detail item.
 *
 * @since 2.7.0
 * @package Give\Receipt
 */
class Section implements Iterator {
	/**
	 * Iterator initial position.
	 *
	 * @var int
	 */
	private $position = 0;

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
	private $lineItems = [];

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
	 * Get line items.
	 *
	 * @return LineItem[]
	 * @since 2.7.0
	 */
	public function getLineItems() {
		return $this->lineItems;
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

		$this->lineItems[] = $listItem;
	}

	/**
	 * Remove line item.
	 *
	 * @param  string $lineItemId
	 *
	 * @since 2.7.0
	 */
	public function removeLineItem( $lineItemId ) {
		/* @var LineItem $lineItem */
		foreach ( $this->lineItems as $index => $lineItem ) {
			if ( $lineItemId === $lineItem->id ) {
				unset( $this->lineItems[ $index ] );
			}
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

	/**
	 * Return current data.
	 *
	 * @return mixed
	 */
	public function current() {
		return $this->lineItems[ $this->position ];
	}

	/**
	 * Update iterator position.
	 */
	public function next() {
		++ $this->position;
	}

	/**
	 * Return iterator position.
	 *
	 * @return bool|float|int|string|void|null
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * Return whether or not valid array position.
	 *
	 * @return bool|void
	 */
	public function valid() {
		return isset( $this->lineItems[ $this->position ] );
	}

	/**
	 * Set iterator position to zero when rewind.
	 */
	public function rewind() {
		$this->position = 0;
	}
}
