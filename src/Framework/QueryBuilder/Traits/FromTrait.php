<?php

namespace Give\Framework\QueryBuilder\Traits;

use Give\Framework\QueryBuilder\From;

/**
 * @unreleased
 */
trait FromTrait
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
                        return "{$from->table} AS {$from->alias}";
                    }

                    return $from->table;
                }, $this->froms)
            )
        ];
    }
}
