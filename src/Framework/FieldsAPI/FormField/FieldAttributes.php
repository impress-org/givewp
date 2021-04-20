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
		return $this->attributes;
	}
}
