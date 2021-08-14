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
			// Cast to a BasicCondition if not already a Condition
			if ( ! $condition instanceof Condition ) {
				$condition = BasicCondition::fromArray( $condition );
			}

			$this->visibilityConditions[] = $condition;
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
			// Cast to a BasicCondition if not already a Condition
			if ( ! $condition instanceof Condition ) {
				$condition = BasicCondition::fromArray( $condition );
			}

			// Invert the condition since the node is visible by default
			$this->visibilityConditions[] = $condition->invert();
		}

		return $this;
	}
}
