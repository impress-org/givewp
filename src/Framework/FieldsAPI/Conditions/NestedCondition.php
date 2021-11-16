<?php

namespace Give\Framework\FieldsAPI\Conditions;

/**
 * @since 2.13.0
 */
class NestedCondition extends Condition {

	/** @var string  */
	const TYPE = 'nested';

	/** @var Condition[] */
	public $conditions = [];

	/** @var string */
	public $boolean;

	/**
	 * @since 2.13.0
	 *
	 * @param Condition[] $conditions
	 * @param string $boolean
	 */
	public function __construct( $conditions, $boolean = 'and' ) {
		$this->conditions = $conditions;
		$this->boolean    = $boolean;
	}

	/**
	 * {@inheritDoc}
	 */
	public function jsonSerialize() {
		return [
			'type'       => static::TYPE,
			'conditions' => $this->conditions,
			'boolean'    => $this->boolean,
		];
	}
}
