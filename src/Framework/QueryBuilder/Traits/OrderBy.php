<?php

namespace Give\Framework\QueryBuilder\Traits;

trait OrderBy {

	/**
	 * @var string
	 */
	public $column;

	/**
	 * @var string
	 */
	public $direction;

	/**
	 * @param string $tableColumn
	 * @param string $direction
	 *
	 * @return $this
	 */
	public function orderBy( $tableColumn, $direction ) {
        if ( strpos($tableColumn, '.')) {
            list( $table, $column ) = explode('.', $tableColumn );
            $this->column = "{$table}.{$column}";
        } else {
            $this->column = $tableColumn;
        }

		$this->direction = $direction;
		return $this;
	}

	public function getOrderBySQL() {
		return $this->column && $this->direction
			? [ "ORDER BY $this->column $this->direction" ]
			: [];
	}
}
