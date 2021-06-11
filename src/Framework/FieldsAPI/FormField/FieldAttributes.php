<?php

namespace Give\Framework\FieldsAPI\FormField;

/**
 * @since 2.10.2
 */
trait FieldAttributes {

	/**
	 * @since 2.10.2
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * @since 2.10.2
	 * @param array $attributes
	 * @return $this
	 */
	public function attributes( $attributes ) {
		$this->attributes = $attributes;
		return $this;
	}

	/**
	 * @since 2.10.2
	 * @return array
	 */
	public function getAttributes() {
		$attributesWithoutClassOrId = $this->attributes;

		foreach ( [ 'class', 'id' ] as $attribute ) {
			if ( in_array( $attribute, $attributesWithoutClassOrId, true ) ) {
				unset( $attributesWithoutClassOrId[ $attribute ] );
			}
		}

		return $attributesWithoutClassOrId;
	}

	/**
	 * Get the class attribute
	 *
	 * @return ?string
	 */
	public function getClassAttribute() {
		return isset( $this->attributes['class'] ) ? $this->attributes['class'] : null;
	}

	/**
	 * Get the ID attribute
	 *
	 * @return ?string
	 */
	public function getIdAttribute() {
		return isset( $this->attributes['class'] ) ? $this->attributes['id'] : null;
	}
}
