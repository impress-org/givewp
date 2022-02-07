<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Concerns\WhereClause;

/**
 * @unreleased
 */
class WhereQueryBuilder
{
    use WhereClause;

    /**
     * @return string[]
     */
    public function getSQL()
    {
        return $this->getWhereSQL();
    }
}
