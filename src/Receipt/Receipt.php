<?php

namespace Give\Receipt;

use Give\Helpers\ArrayDataSet;
use InvalidArgumentException;

/**
 * Class Receipt
 *
 * This class represent receipt as object.
 * Receipt can have multiple detail group and detail group can has multiple detail item.
 * You can add your own logic to render receipt because it does not return any UI item.
 *
 * @since 2.7.0
 * @package Give\Receipt
 */
abstract class Receipt {
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
	 * Get receipt sections.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	public function getSections() {
		return ArrayDataSet::convertToObject( $this->sectionList );
	}

	/**
	 * Get receipt sections.
	 *
	 * @param  string $sectionId
	 *
	 * @return array|null
	 * @since 2.7.0
	 */
	public function getSection( $sectionId ) {
		return isset( $this->sectionList[ $sectionId ] ) ? $this->sectionList[ $sectionId ] : null;
	}

	/**
	 * Add detail group.
	 *
	 * @param  array $section
	 *
	 * @since 2.7.0
	 */
	public function addSection( $section ) {
		$this->validateSection( $section );

		$this->sectionList[ $section['id'] ] = $section;
	}

	/**
	 * Add detail group.
	 *
	 * @param  string $sectionId
	 * @param  array  $listItem
	 *
	 * @since 2.7.0
	 */
	public function addLineItem( $sectionId, $listItem ) {
		$this->validateLineItem( $listItem );

		$this->sectionList[ $sectionId ]['lineItem'][ $listItem['id'] ] = $listItem;
	}

	/**
	 * Remove receipt section.
	 *
	 * @param  string $sectionId
	 *
	 * @since 2.7.0
	 */
	public function removeSection( $sectionId ) {
		if ( in_array( $sectionId, $this->sectionList, true ) ) {
			unset( $this->sectionList[ array_search( $sectionId, $this->sectionList, true ) ] );
		}
	}

	/**
	 * Validate section.
	 *
	 * @param array $array
	 * @since 2.7.0
	 */
	protected function validateSection( $array ) {
		$required = [ 'id' ];
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( __( 'Invalid receipt section. Please provide valid section id', 'give' ) );
		}
	}

	/**
	 * Validate line item.
	 *
	 * @param array $array
	 * @since 2.7.0
	 */
	protected function validateLineItem( $array ) {
		$required = [ 'label', 'value' ];
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( __( 'Invalid receipt section line item. Please provide valid line item id, label, and value.', 'give' ) );
		}
	}
}
