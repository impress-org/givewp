<?php

namespace Give\Framework\FieldsAPI\FormField;

trait FieldRequired {

	/** @var bool */
	protected $required = false;

	/**
	 * @param bool $requried
	 * @return $this
	 */
	public function required( $required = true ) {
		$this->required = $required;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isRequired() {
		return $this->required;
	}

	public function getRequiredError() {
		return [
			'error_id'      => $this->name,
			'error_message' => __( 'Please enter a value for ' . $this->name, 'give' ),
		];
	}
}
