<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Traits\FromTrait;
use Give\Framework\QueryBuilder\Traits\GroupByTrait;
use Give\Framework\QueryBuilder\Traits\JoinTrait;
use Give\Framework\QueryBuilder\Traits\LimitTrait;
use Give\Framework\QueryBuilder\Traits\MetaTrait;
use Give\Framework\QueryBuilder\Traits\OrderByTrait;
use Give\Framework\QueryBuilder\Traits\SelectTrait;
use Give\Framework\QueryBuilder\Traits\WhereTrait;

/**
 * @unreleased
 */
class QueryBuilder
{
    use SelectTrait;
    use FromTrait;
    use JoinTrait;
    use WhereTrait;
    use OrderByTrait;
    use GroupByTrait;
    use LimitTrait;
    use MetaTrait;

    /**
     * @return string
     */
    public function getSQL()
    {
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
