<?php

namespace Give\Framework\QueryBuilder\Traits;

trait Select {

	/**
	 * @var array
	 */
	public $selects = [];

    /**
     * @param array $selects
     *
     * @return $this
     */
	public function select( $selects ) {
		$this->selects = array_map(function($select) {
			if( is_array( $select ) ) {
				list( $column, $alias ) = $select;
			} else {
				$column = $alias = $select;
			}
			return [ $column, $alias ];
		}, $selects);
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getSelectSQL() {
		return [
			'SELECT ' . implode(', ', array_map( function( $select ) {
				list( $tableColumn, $alias ) = $select;
                if ( strpos($tableColumn, '.')) {
                    list( $table, $column ) = explode('.', $tableColumn );
                    return "{$this->alias( $table )}.$column AS $alias";
                }

                return "$tableColumn AS $alias";

			}, $this->selects) )
		];
	}
}
