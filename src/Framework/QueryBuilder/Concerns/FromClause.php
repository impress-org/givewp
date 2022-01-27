<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Models\From;

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

    protected function getFromSQL()
    {
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
