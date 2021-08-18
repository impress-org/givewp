<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FieldsAPI\Conditions\BasicCondition;

/**
 * @unreleased
 */
trait HasVisibilityConditions {

	/**
	 * The node is visible by default. These are the conditions for it to remain visible.
	 *
	 * @unreleased
	 *
	 * @var BasicCondition[]
	 */
	protected $visibilityConditions = [];

	/**
	 * Get the visibility conditions.
	 *
	 * @unreleased
	 *
	 * @return BasicCondition[]
	 */
	public function getVisibilityConditions() {
		return $this->visibilityConditions;
	}

	/**
	 * Set a condition for showing the node.
	 *
	 * @unreleased
	 *
	 * @param string $field
	 * @param string $operator
	 * @param mixed $value
	 * @param string $boolean
	 *
	 * @return $this
	 */
	public function showIf( $field, $operator, $value, $boolean = 'and' ) {
		$this->visibilityConditions[] = new BasicCondition( $field, $operator, $value, $boolean );

		return $this;
	}

	/**
	 * Set multiple conditions for showing the node.
	 *
	 * @unreleased
	 *
	 * @param BasicCondition|array ...$conditions
	 *
	 * @return $this
	 */
	public function showWhen( ...$conditions ) {
		foreach ( $conditions as $condition ) {
			$this->visibilityConditions[] = $this->normalizeCondition( $condition );
		}

		return $this;
	}

	/**
	 * Normalize the condition if in array format.
	 *
	 * @unreleased
	 *
	 * @param BasicCondition|array $condition
	 *
	 * @return BasicCondition
	 *
	 * @throws InvalidArgumentException
	 */
	protected function normalizeCondition( $condition ) {
		if ( $condition instanceof BasicCondition ) {
			return $condition;
		}

		if ( is_array( $condition ) ) {
			return new BasicCondition( ...$condition );
		}

		throw new InvalidArgumentException( 'Parameter $condition must be a BasicCondition or an array.' );
	}
}
