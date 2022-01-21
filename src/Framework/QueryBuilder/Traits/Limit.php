<?php

namespace Give\Framework\QueryBuilder\Traits;

trait Limit {

	/**
	 * @var int
	 */
	public $limit;

	/**
	 * @param int $limit
	 * @return $this
	 */
	public function limit( $limit ) {
		$this->limit = $limit;
		return $this;
	}

	public function getLimitSQL() {
		return $this->limit
			   ? [ "LIMIT {$this->limit}" ]
			   : [];
	}
}
