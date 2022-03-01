<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Concerns\WhereClause;

/**
 * @since 2.19.0
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
