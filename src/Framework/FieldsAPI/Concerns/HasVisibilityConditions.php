<?php

namespace Give\Framework\FieldsAPI\Concerns;

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
	 * Set conditions for showing the node.
	 *
	 * @unreleased
	 *
	 * @param BasicCondition|array ...$conditions
	 *
	 * @return $this
	 */
	public function showIf( ...$conditions ) {
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
	 */
	protected function normalizeCondition( $condition ) {
		if ( ! $condition instanceof BasicCondition && is_array( $condition ) ) {
			$condition = new BasicCondition( ...$condition );
		}

		// TODO: Probably should throw an error if not an array or Condition

		return $condition;
	}
}
