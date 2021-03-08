<?php

namespace Give\Framework\FieldsAPI\FormField;

use Give\Framework\FieldsAPI\FormField\FieldTypes;

trait FieldOptions {

	/** @var array */
	protected $options = [];

	public function supportsOptions() {
		return in_array(
			$this->getType(),
			[
				FieldTypes::TYPE_SELECT,
				FieldTypes::TYPE_RADIO,
			]
		);
	}

	public function options( $options ) {
		$this->options = $options;
		return $this;
	}

	public function getOptions() {
		return $this->options;
	}
}
