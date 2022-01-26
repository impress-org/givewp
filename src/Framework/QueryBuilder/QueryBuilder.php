<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Concerns\FromClause;
use Give\Framework\QueryBuilder\Concerns\GroupByStatement;
use Give\Framework\QueryBuilder\Concerns\HavingClause;
use Give\Framework\QueryBuilder\Concerns\JoinClause;
use Give\Framework\QueryBuilder\Concerns\LimitStatement;
use Give\Framework\QueryBuilder\Concerns\MetaQuery;
use Give\Framework\QueryBuilder\Concerns\OrderByStatement;
use Give\Framework\QueryBuilder\Concerns\SelectStatement;
use Give\Framework\QueryBuilder\Concerns\WhereClause;

/**
 * @unreleased
 */
class QueryBuilder
{
    use FromClause;
    use GroupByStatement;
    use HavingClause;
    use JoinClause;
    use LimitStatement;
    use MetaQuery;
    use OrderByStatement;
    use SelectStatement;
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
            $this->getLimitSQL()
        );

        // Trim triple doubles spaces added by DB::prepare
        return str_replace(
            ['   ', '  '],
            ' ',
            implode(' ', $sql)
        );
    }

    /**
     * Get results
     *
     * @param  string ARRAY_A|ARRAY_N|OBJECT|OBJECT_K $output
     *
     * @return array|object|null
     */
    public function getAll($output = OBJECT)
    {
        return DB::get_results($this->getSQL(), $output);
    }

    /**
     * Get row
     *
     * @param  string ARRAY_A|ARRAY_N|OBJECT|OBJECT_K $output
     *
     * @return array|object|null
     */
    public function get($output = OBJECT)
    {
        return DB::get_row($this->getSQL(), $output);
    }
}
