<?php

namespace Give\Framework\QueryBuilder\Traits;

/**
 * @unreleased
 */
trait GroupByTrait {

	/**
	 * @var string
	 */
	protected $groupByColumns = [];

	/**
	 * @return $this
	 */
	public function groupBy( $tableColumn ) {
		$this->groupByColumns[] = $tableColumn;

		return $this;
	}

	public function getGroupBySQL() {
		return ! empty( $this->groupByColumns )
			? [
				'GROUP BY ' . implode( ',', array_map( function ( $column ) {
					return $column;
				}, $this->groupByColumns ) )
			]
			: [];
	}
}
