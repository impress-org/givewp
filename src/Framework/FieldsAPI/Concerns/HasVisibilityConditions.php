<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 */
trait HasVisibilityConditions {

	/**
	 * The node is visible by default. These are the conditions for it to remain visible.
	 *
	 * @unreleased
	 *
	 * @var Condition[]
	 */
	protected $visibilityConditions = [];

	/**
	 * Get the visibility conditions.
	 *
	 * @unreleased
	 *
	 * @return Condition[]
	 */
	public function getVisibilityConditions() {
		return $this->visibilityConditions;
	}

	/**
	 * Set conditions for showing the node.
	 *
	 * @unreleased
	 *
	 * @param Condition|array ...$conditions
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
	 * Set conditions for hiding the node.
	 *
	 * @unreleased
	 *
	 * @param Condition|array ...$conditions
	 *
	 * @return $this
	 */
	public function hideIf( ...$conditions ) {
		foreach ( $conditions as $condition ) {
			// Invert the condition since the node is visible by default
			$this->visibilityConditions[] = $this->normalizeCondition( $condition )->invert();
		}

		return $this;
	}

	/**
	 * Normalize the condition if in array format.
	 *
	 * @unreleased
	 *
	 * @param Condition|array $condition
	 *
	 * @return Condition
	 */
	protected function normalizeCondition( $condition ) {
		if ( ! $condition instanceof Condition && is_array( $condition ) ) {
			// If the first array item is also an array, then it is nested, otherwise it is basic.
			$conditionClass = is_array( $condition[0] ) ? NestedCondition::class : BasicCondition::class;

			// If we are working with a nested condition, then we need to ensure $conditions passed are already cast to a Condition
			if ( $conditionClass === NestedCondition::class ) {
				$condition[0] = array_map( [ $this, 'normalizeCondition' ], $condition[0] );
			}

			$condition = new $conditionClass( ...$condition );
		}

		// TODO: Probably should throw an error if not an array or Condition

		return $condition;
	}
}
