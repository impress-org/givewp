<?php

namespace Give\Framework\QueryBuilder\Concerns;

/**
 * @unreleased
 */
trait LimitStatement
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @param  int  $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = (int)$limit;

        return $this;
    }

    protected function getLimitSQL()
    {
        return $this->limit
            ? ["LIMIT {$this->limit}"]
            : [];
    }
}
