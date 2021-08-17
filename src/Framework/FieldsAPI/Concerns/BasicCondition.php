<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 */
class BasicCondition extends Condition {

	/** @var string */
	const TYPE = 'basic';

	/** @var array */
	const OPERATORS_AND_INVERSIONS = [
		'=' => '!=',
		'!=' => '=',
		'>' => '<=',
		'<' => '>=',
		'>=' => '<',
		'<=' => '>',
	];

	/** @var string */
	public $field;

	/** @var mixed */
	public $value;

	/** @var string */
	public $operator;

	/** @var string */
	public $boolean;

	public function __construct( $field, $operator, $value, $boolean = 'and' ) {
		$this->field = $field;
		$this->operator = $operator;
		$this->value = $value;
		$this->boolean = $boolean;
	}

	/**
	 * {@inheritDoc}
	 */
	public function invert() {
		$this->operator = static::OPERATORS_AND_INVERSIONS[ $this->operator ];

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function jsonSerialize() {
		return [
			'type' => static::TYPE,
			'field' => $this->field,
			'value' => $this->value,
			'operator' => $this->operator,
			'boolean' => $this->boolean,
		];
	}
}
