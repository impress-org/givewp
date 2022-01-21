<?php

namespace Give\Framework\QueryBuilder\Traits;

trait Where {

	/**
	 * @var string
	 */
	public $wheres = [];

	/**
	 * @param string $column
	 * @param string $comparator
	 * @param string $value
	 * @param string $logicalOperator
	 *
	 * @return $this
	 */
	private function setWhere( $column, $comparator, $value, $logicalOperator ) {
		$this->wheres[] = [ $column, $comparator, $value, $logicalOperator ];
		return $this;
	}

	/**
	 * @param string $column
	 * @param string $comparator
	 * @param string $value
	 * @return $this
	 */
	public function where( $column, $value, $comparator = '=' ) {
		return $this->setWhere( $column, $comparator, $value, 'AND' );
	}

	/**
	 * @param string $column
	 * @param string $comparator
	 * @param string $value
	 *
	 * @return $this
	 */
	public function orWhere( $column, $value,  $comparator = '=' ) {
		return $this->setWhere( $column, $comparator, $value, 'OR' );
	}

	public function getWhereSQL() {
		$sql = array_map(function( $where ) {
			list( $tableColumn, $comparator, $value, $operator ) = $where;
			list( $table, $column ) = explode('.', $tableColumn );
			return "{$operator} {$this->alias($table)}.$column $comparator '$value'";
		}, $this->wheres);
		return array_merge([ 'WHERE 1' ], $sql );
	}
}
