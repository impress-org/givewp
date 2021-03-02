<?php

namespace Give\Framework\FieldsAPI\FormField;

trait FieldLabel {

	/** @var string */
	protected $label;

	/**
	 * @param string $label
	 * @return $this
	 */
	public function label( $label ) {
		$this->label = $label;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
}
