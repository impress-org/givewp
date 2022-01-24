<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\QueryBuilder\From;

/**
 * @unreleased
 */
trait FromClause
{
    /**
     * @var From[]
     */
    protected $froms = [];

    /**
     * @param  string  $table
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function from($table, $alias = null)
    {
        $this->froms[] = new From($table, $alias);

        return $this;
    }

    public function getFromSQL()
    {
        return [
            'FROM ' . implode(
                ', ',
                array_map(function (From $from) {
                    if ($from->alias) {
                        return sprintf(
                            '%s AS %s',
                            $from->table,
                            $from->alias
                        );
                    }

                    return $from->table;
                }, $this->froms)
            )
        ];
    }
}
