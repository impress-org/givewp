<?php

namespace Give\Framework\FieldsAPI\Conditions;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

/**
 * @since 2.13.0
 */
class BasicCondition extends Condition {

	const OPERATORS = [ '=', '!=', '>', '>=', '<', '<=' ];

	const BOOLEANS = [ 'and', 'or' ];

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

	/**
	 * Create a new BasicCondition.
	 *
	 * @since 2.13.0
	 *
	 * @param string $field
	 * @param string $operator
	 * @param mixed $value
	 * @param string $boolean
	 */
	public function __construct( $field, $operator, $value, $boolean = 'and' ) {
		if ( $this->invalidOperator( $operator ) ) {
			throw new InvalidArgumentException( "Invalid operator: $operator. Must be one of: " . implode( ', ', static::OPERATORS ) );
		}

		if ( $this->invalidBoolean( $boolean ) ) {
			throw new InvalidArgumentException( "Invalid boolean: $boolean. Must be one of: " . implode( ', ', static::BOOLEANS ) );
		}

		$this->field    = $field;
		$this->operator = $operator;
		$this->value    = $value;
		$this->boolean  = $boolean;
	}

	/**
	 * Check if the provided operator is invalid.
	 *
	 * @since 2.13.0
	 *
	 * @param string $operator
	 *
	 * @return bool
	 */
	protected function invalidOperator( $operator ) {
		return ! in_array( $operator, static::OPERATORS, true );
	}

	/**
	 * Check if the provided boolean is invalid.
	 *
	 * @since 2.13.0
	 *
	 * @param $boolean
	 *
	 * @return bool
	 */
	protected function invalidBoolean( $boolean ) {
		return ! in_array( $boolean, static::BOOLEANS, true );
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
