<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasPlaceholder {

	/** @var string*/
	protected $placeholder;

	/**
	 * Set the placeholder value.
	 *
	 * @param string $placeholder
	 *
	 * @return $this
	 */
	public function placeholder( $placeholder ) {
		$this->placeholder = $placeholder;
		return $this;
	}

	/**
	 * Get the placeholder value.
	 *
	 * @return string
	 */
	public function getPlaceholder() {
		return $this->placeholder;
	}
}
