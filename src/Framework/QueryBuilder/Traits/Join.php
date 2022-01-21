<?php

namespace Give\Framework\QueryBuilder\Traits;

trait Join {

	/**
	 * @var array [[ table, foreignKey, primaryKey ]]
	 */
	protected $joins = [];

	/**
	 * @param string $table
	 * @param string $foreignKey
	 * @param string $primaryKey
	 * @return $this
	 */
	public function join( $table, $foreignKey, $primaryKey, $joinType = '' ) {
		$this->joins[] = [ $this->alias( $table ), $foreignKey, $primaryKey, $joinType ];
		return $this;
	}

    /**
     * @return string[]
     */
    public function getJoinSQL()
    {
        return array_map(function ($join) {
            list($table, $foreignKey, $primaryKey, $joinType) = $join;

            if (strpos($table, ' ')) {
                list($table, $alias) = explode(' ', $table);

                return "{$joinType} JOIN {$table} {$alias} ON {$this->from}.$foreignKey = $alias.$primaryKey";
            }

            return "{$joinType} JOIN {$table} ON {$this->from}.$foreignKey = $table.$primaryKey";
        }, $this->joins);
    }
}
