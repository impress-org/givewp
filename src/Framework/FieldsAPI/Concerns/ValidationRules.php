<?php

namespace Give\Framework\FieldsAPI\Concerns;

use JsonSerializable;

class ValidationRules implements JsonSerializable {

	/** @var array */
	protected $rules;

	/**
	 * ValidationRules constructor.
	 *
	 * @param array $rules
	 */
	public function __construct( $rules = [] ) {
		$this->rules = $rules;
	}

	/**
	 * Set a rule.
	 *
	 * @param string $rule
	 * @param mixed $value
	 * @return $this
	 */
	public function rule( $rule, $value ) {
		$this->rules[ $rule ] = $value;

		return $this;
	}

	/**
	 * Get a rule.
	 *
	 * @param string $rule
	 * @return mixed
	 */
	public function getRule( $rule ) {
		return array_key_exists( $rule, $this->rules )
			? $this->rules[ $rule ]
			: null;
	}

	/**
	 * Forget a rule.
	 *
	 * @param string $rule
	 * @return $this
	 */
	public function forgetRule( $rule ) {
		if ( array_key_exists( $rule, $this->rules ) ) {
			unset( $this->rules[ $rule ] );
		}

		return $this;
	}

	/**
	 * Get all the rules.
	 *
	 * @return array
	 */
	public function all() {
		return $this->rules;
	}

	/**
	 * {@inheritdoc}}
	 */
	public function jsonSerialize() {
		return (object) $this->all();
	}
}
