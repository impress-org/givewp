<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Concerns\Aggregate;
use Give\Framework\QueryBuilder\Concerns\CRUD;
use Give\Framework\QueryBuilder\Concerns\FromClause;
use Give\Framework\QueryBuilder\Concerns\GroupByStatement;
use Give\Framework\QueryBuilder\Concerns\HavingClause;
use Give\Framework\QueryBuilder\Concerns\JoinClause;
use Give\Framework\QueryBuilder\Concerns\LimitStatement;
use Give\Framework\QueryBuilder\Concerns\MetaQuery;
use Give\Framework\QueryBuilder\Concerns\OffsetStatement;
use Give\Framework\QueryBuilder\Concerns\OrderByStatement;
use Give\Framework\QueryBuilder\Concerns\SelectStatement;
use Give\Framework\QueryBuilder\Concerns\TablePrefix;
use Give\Framework\QueryBuilder\Concerns\UnionOperator;
use Give\Framework\QueryBuilder\Concerns\WhereClause;

/**
 * @since 2.19.0
 */
class QueryBuilder
{
    use Aggregate;
    use CRUD;
    use FromClause;
    use GroupByStatement;
    use HavingClause;
    use JoinClause;
    use LimitStatement;
    use MetaQuery;
    use OffsetStatement;
    use OrderByStatement;
    use SelectStatement;
    use TablePrefix;
    use UnionOperator;
    use WhereClause;

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
            $this->getHavingSQL(),
            $this->getOrderBySQL(),
            $this->getLimitSQL(),
            $this->getOffsetSQL(),
            $this->getUnionSQL()
        );

        // Trim double spaces added by DB::prepare
        return str_replace(
            ['   ', '  '],
            ' ',
            implode(' ', $sql)
        );
    }
}
