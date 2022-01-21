<?php

namespace Give\Framework\QueryBuilder\Traits;

trait From {

	/**
	 * @var string
	 */
	public $from;

	/**
	 * @param string $table
	 * @return $this
	 */
	public function from( $table ) {
		$this->from = $this->alias( $table );
		return $this;
	}

	public function getFromSQL() {
		return [ "FROM {$this->from}" ];
	}
}
