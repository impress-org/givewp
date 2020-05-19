<?php

namespace Give\Receipt;

use ArrayAccess;
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
class Section implements Iterator, ArrayAccess {
	/**
	 * Iterator initial position.
	 *
	 * @var int
	 */
	private $position = 0;

	/**
	 * Array of line item ids to use in Iterator.
	 * Note: this property helps to iterate over associative array.
	 *
	 * @var int
	 */
	private $lineItemIds = [];

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
	 * @param  string $id
	 * @param  string $label
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
	 * @param  array $lineItem
	 *
	 * @return LineItem
	 * @since 2.7.0
	 */
	public function addLineItem( $lineItem ) {
		$this->validateLineItem( $lineItem );

		$icon = isset( $lineItem['icon'] ) ? $lineItem['icon'] : '';

		$lineItemObj = new LineItem( $lineItem['id'], $lineItem['label'], $lineItem['value'], $icon );

		$this->lineItems[ $lineItemObj->id ] = $lineItemObj;
		$this->lineItemIds[]                 = $lineItemObj->id;

		return $lineItemObj;
	}

	/**
	 * Remove line item.
	 *
	 * @param  string $lineItemId
	 *
	 * @since 2.7.0
	 */
	public function removeLineItem( $lineItemId ) {
		foreach ( $this->lineItems as $index => $lineItem ) {
			if ( $lineItemId === $lineItem->id ) {
				unset( $this->lineItems[ $index ], $this->lineItemIds[ $index ] );
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
			throw new InvalidArgumentException(
				__(
					'Invalid receipt section line item. Please provide valid line item id, label, and value.',
					'give'
				)
			);
		}
	}

	/**
	 * Return current data.
	 *
	 * @return mixed
	 */
	public function current() {
		return $this->lineItems[ $this->lineItemIds[ $this->position ] ];
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
		return isset( $this->lineItemIds[ $this->position ] );
	}

	/**
	 * Set iterator position to zero when rewind.
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * Set line item.
	 *
	 * @param  string $offset  LineItem ID.
	 * @param  array  $value  LineItem Data.
	 *
	 * @since 2.7.0
	 */
	public function offsetSet( $offset, $value ) {
		$this->addLineItem( $value );
	}

	/**
	 * Return whether or not line item id exist in line.
	 *
	 * @param  string $offset  LineItem ID.
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public function offsetExists( $offset ) {
		return isset( $this->lineItemIds[ $offset ] );
	}

	/**
	 * Remove line item from line.
	 *
	 * @param  string $offset  LineItem ID.
	 *
	 * @since 2.7.0
	 */
	public function offsetUnset( $offset ) {
		$this->removeLineItem( $offset );
	}

	/**
	 * Get line item.
	 *
	 * @param  string $offset  LineItem ID.
	 *
	 * @return LineItem|null
	 * @since 2.7.0
	 */
	public function offsetGet( $offset ) {
		return isset( $this->lineItems[ $offset ] ) ? $this->lineItems[ $offset ] : null;
	}
}
