<?php

namespace Give\Framework\FieldsAPI\Conditions;

/**
 * @unreleased
 */
class BasicCondition extends Condition {

	/** @var string */
	const TYPE = 'basic';

	/** @var string */
	public $field;

	/** @var mixed */
	public $value;

	/** @var string */
	public $operator;

	/** @var string */
	public $boolean;

	public function __construct( $field, $operator, $value, $boolean = 'and' ) {
		$this->field    = $field;
		$this->operator = $operator;
		$this->value    = $value;
		$this->boolean  = $boolean;
	}

	/**
	 * {@inheritDoc}
	 */
	public function jsonSerialize() {
		return [
			'type'     => static::TYPE,
			'field'    => $this->field,
			'value'    => $this->value,
			'operator' => $this->operator,
			'boolean'  => $this->boolean,
		];
	}
}
