<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Traits\Aliases;
use Give\Framework\QueryBuilder\Traits\From;
use Give\Framework\QueryBuilder\Traits\GroupBy;
use Give\Framework\QueryBuilder\Traits\Join;
use Give\Framework\QueryBuilder\Traits\Limit;
use Give\Framework\QueryBuilder\Traits\OrderBy;
use Give\Framework\QueryBuilder\Traits\Select;
use Give\Framework\QueryBuilder\Traits\Where;

class QueryBuilder {

	use Aliases;
	use Select;
	use From;
	use Join;
	use Where;
	use OrderBy;
	use GroupBy;
	use Limit;

	/**
	 * @return string
	 */
	public function getSQL() {

		$sql = array_merge(
			$this->getSelectSQL(),
			$this->getFromSQL(),
			$this->getJoinSQL(),
			$this->getWhereSQL(),
			$this->getGroupBySQL(),
			$this->getOrderBySQL(),
			$this->getLimitSQL()
		);

		return implode(' ', $sql);
	}
}
