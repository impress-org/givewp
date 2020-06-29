<?php

namespace Give\Receipt;

use ArrayAccess;
use Iterator;

/**
 * Class Receipt
 *
 * This class represent receipt as object.
 * Receipt can have multiple sections and sections can have multiple line items.
 *
 * @since 2.7.0
 * @package Give\Receipt
 */
abstract class Receipt implements Iterator, ArrayAccess {
	/**
	 * Iterator initial position.
	 *
	 * @var int
	 */
	protected $position = 0;

	/**
	 * Receipt Heading.
	 *
	 * @since 2.7.0
	 * @var string $heading
	 */
	public $heading = '';

	/**
	 * Receipt message.
	 *
	 * @since 2.7.0
	 * @var string $message
	 */
	public $message = '';

	/**
	 * Receipt details group class names.
	 *
	 * @since 2.7.0
	 * @var array
	 */
	protected $sectionList = [];

	/**
	 * Array of section ids to use for Iterator.
	 * Note: this property helps to iterate over associative array.
	 *
	 * @var int
	 */
	protected $sectionIds = [];

	/**
	 * Get receipt sections.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	public function getSections() {
		return $this->sectionList;
	}

	/**
	 * Add receipt section.
	 *
	 * @param  array  $section
	 * @param  string $position Position can be set either "before" or "after" to insert section at specific position.
	 * @param  string $sectionId
	 *
	 * @return Section
	 *
	 * @since 2.7.0
	 */
	public function addSection( $section, $position = '', $sectionId = '' ) {
		$this->validateSection( $section );

		// Add default label.
		$label = isset( $section['label'] ) ? $section['label'] : '';

		$sectionObj = new Section( $section['id'], $label );

		if ( isset( $this->sectionList[ $sectionId ] ) && in_array( $position, [ 'before', 'after' ] ) ) {
			// Insert line item at specific position.
			$tmp    = [];
			$tmpIds = [];

			foreach ( $this->sectionList as $id => $data ) {
				if ( 'after' === $position ) {
					$tmp[ $id ] = $data;
					$tmpIds[]   = $id;
				}

				if ( $id === $sectionId ) {
					$tmp[ $sectionObj->id ] = $sectionObj;
					$tmpIds[]               = $sectionObj->id;
				}

				if ( 'before' === $position ) {
					$tmp[ $id ] = $data;
					$tmpIds[]   = $id;
				}
			}

			$this->sectionList = $tmp;
			$this->sectionIds  = $tmpIds;
		} else {
			$this->sectionList[ $sectionObj->id ] = $sectionObj;
			$this->sectionIds[]                   = $sectionObj->id;
		}

		return $sectionObj;
	}

	/**
	 * Remove receipt section.
	 *
	 * @param  string $sectionId
	 *
	 * @since 2.7.0
	 */
	public function removeSection( $sectionId ) {
		$this->offsetUnset( $sectionId );
	}

	/**
	 * Set section.
	 *
	 * @param  string $offset Section ID.
	 * @param  array  $value   Section Data.
	 * @since 2.7.0
	 */
	public function offsetSet( $offset, $value ) {
		$this->addSection( $value );
	}

	/**
	 * Return whether or not session id exist in list.
	 *
	 * @param  string $offset Section ID.
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	public function offsetExists( $offset ) {
		return isset( $this->sectionList[ $offset ] );
	}

	/**
	 * Remove section from list.
	 *
	 * @param  string $offset Section ID.
	 * @since 2.7.0
	 */
	public function offsetUnset( $offset ) {
		if ( $this->offsetExists( $offset ) ) {
			unset( $this->sectionList[ $offset ] );
			$this->sectionIds = array_keys( $this->sectionList );
		}
	}

	/**
	 * Get section.
	 *
	 * @param  string $offset Session ID.
	 *
	 * @return Section|null
	 * @since 2.7.0
	 */
	public function offsetGet( $offset ) {
		return isset( $this->sectionList[ $offset ] ) ? $this->sectionList[ $offset ] : null;
	}

	/**
	 * Return current data when iterate or data.
	 *
	 * @return mixed
	 * @since 2.7.0
	 */
	public function current() {
		return $this->sectionList[ $this->sectionIds[ $this->position ] ];
	}

	/**
	 * Update iterator position.
	 *
	 * @since 2.7.0
	 */
	public function next() {
		++ $this->position;
	}

	/**
	 * Return iterator position.
	 *
	 * @return int
	 * @since 2.7.0
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * Return whether or not valid array position.
	 *
	 * @return bool|void
	 * @since 2.7.0
	 */
	public function valid() {
		return isset( $this->sectionIds[ $this->position ] );
	}
}
