<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Clauses\From;
use Give\Framework\QueryBuilder\Clauses\RawSQL;

/**
 * @since 2.19.0
 */
trait FromClause
{
    /**
     * @var From[]
     */
    protected $froms = [];

    /**
     * @param  string|RawSQL  $table
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function from($table, $alias = null)
    {
        $this->froms[] = new From($table, $alias);

        return $this;
    }

    /**
     * @return array|string[]
     */
    protected function getFromSQL()
    {
        if (empty($this->froms)) {
            return [];
        }

        return [
            'FROM ' . implode(
                ', ',
                array_map(function (From $from) {
                    if ($from->alias) {
                        return DB::prepare(
                            '%1s AS %2s',
                            $from->table,
                            $from->alias
                        );
                    }

                    return DB::prepare('%1s', $from->table);
                }, $this->froms)
            )
        ];
    }
}
