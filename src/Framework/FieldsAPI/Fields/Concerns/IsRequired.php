<?php

namespace Give\Framework\FieldsAPI\Fields\Concerns;

trait IsRequired {

	/** @var bool */
	protected $required = false;

	/**
	 * {@inheritdoc}
	 */
	public function required( $required = true ) {
		$this->required = $required;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isRequired() {
		return $this->required;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRequiredError() {
		return [
			'error_id'      => $this->name,
			'error_message' => __( 'Please enter a value for ' . $this->name, 'give' ),
		];
	}
}
