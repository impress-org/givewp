<?php

namespace Give\Framework\Models;

use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\QueryBuilder\Clauses\Having;
use Give\Framework\QueryBuilder\Clauses\RawSQL;
use Give\Framework\QueryBuilder\Clauses\Select;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 2.19.6
 *
 * @template M
 */
class ModelQueryBuilder extends QueryBuilder
{
    /**
     * @var class-string<M>
     */
    protected $model;

    /**
     * @param class-string<M> $modelClass
     */
    public function __construct($modelClass)
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("$modelClass must be an instance of " . Model::class);
        }

        $this->model = $modelClass;
    }

    /**
     * Returns the number of rows returned by a query
     *
     * @since TBD Honor an explicit column in grouped counts by summing the column's non-null values per group.
     * @since TBD Preserve SELECT aliases referenced by HAVING when counting a grouped query.
     * @since TBD Count the groups of a grouped query rather than the first group's rows.
     * @since 2.24.0
     *
     * @param  null|string  $column
     */
    public function count($column = null): int
    {
        $column = ( ! $column || $column === '*') ? '1' : trim($column);

        /*
         * A grouped query returns one row per group and get_row() reads only the first of them,
         * so the number of groups is what the caller is actually asking for.
         */
        if ($this->groupByColumns) {
            return +DB::get_row($this->getGroupCountSQL($column))->count;
        }

        if ('1' === $column) {
            $this->selects = [];
        }
        $this->selects[] = new RawSQL('SELECT COUNT(%1s) AS count', $column);

        return +parent::get()->count;
    }

    /**
     * @since 3.6.0
     *
     * @param int $perPage
     * @param int $page
     *
     * @return ModelQueryBuilder
     */
    public function paginate($perPage, $page = 1): ModelQueryBuilder
    {
        return $this
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);
    }

    /**
     * Get row
     *
     * @since 2.19.6
     *
     * @return M|null
     *
     * @param int $output For inheritance compatibility only, unused.
     */
    public function get($output = OBJECT)
    {
        $row = DB::get_row($this->getSQL(), OBJECT);

        if (!$row) {
            return null;
        }

        return $this->getRowAsModel($row);
    }

    /**
     * Get results
     *
     * @since 2.19.6
     *
     * @return M[]|null
     *
     * @param int $output For inheritance compatibility only, unused.
     */
    public function getAll($output = OBJECT)
    {
        $results = DB::get_results($this->getSQL(), OBJECT);

        if (!$results) {
            return null;
        }

        if (isset($this->model)) {
            return $this->getAllAsModel($results);
        }

        return $results;
    }

    /**
     * Get row as model
     *
     * @since 2.19.6
     *
     * @param object|null $row
     *
     * @return M|null
     */
    protected function getRowAsModel($row)
    {
        $model = $this->model;

        if (!method_exists($model, 'fromQueryBuilderObject')) {
            throw new InvalidArgumentException("fromQueryBuilderObject missing from $model");
        }

        return $model::fromQueryBuilderObject($row);
    }

    /**
     * Get results as models
     *
     * @since 2.19.6
     *
     * @param object[] $results
     *
     * @return M[]|null
     */
    protected function getAllAsModel($results)
    {
        /** @var ModelCrud $model */
        $model = $this->model;

        if (!method_exists($model, 'fromQueryBuilderObject')) {
            throw new InvalidArgumentException("fromQueryBuilderObject missing from $model");
        }

        return array_map(static function ($object) use ($model) {
            return $model::fromQueryBuilderObject($object);
        }, $results);
    }

    /**
     * Wraps the grouped query so that its rows, one per group, are what gets counted. A
     * COUNT(DISTINCT ...) over the grouped columns would drop every group holding a NULL.
     *
     * An explicit column counts the column's non-null values, so each group contributes its
     * non-null count and the wrapper sums them instead of counting groups.
     *
     * The grouped columns are aliased because a derived table rejects duplicate column names, and
     * the ordering and paging are dropped because neither changes the number of groups. SELECT
     * entries whose aliases a HAVING clause references are kept, since the replaced select list
     * would otherwise leave HAVING pointing at an alias that no longer exists.
     *
     * @since TBD
     *
     * @param  string  $column
     */
    private function getGroupCountSQL($column = null): string
    {
        $innerSelects = [];

        if ($column && '1' !== $column) {
            $innerSelects[] = DB::prepare('COUNT(%1s) AS nonNullCount', $column);
        }

        foreach ($this->groupByColumns as $index => $groupByColumn) {
            $innerSelects[] = "{$groupByColumn} AS groupedColumn{$index}";
        }

        foreach ($this->getHavingReferencedSelects() as $select) {
            $innerSelects[] = $select;
        }

        $this->selects = [new RawSQL('SELECT ' . implode(', ', $innerSelects))];
        $this->orderBys = [];
        $this->limit = null;
        $this->offset = null;

        if ($column && '1' !== $column) {
            return "SELECT SUM(nonNullCount) AS count FROM ({$this->getSQL()}) AS groupedQuery";
        }

        return "SELECT COUNT(*) AS count FROM ({$this->getSQL()}) AS groupedQuery";
    }

    /**
     * Renders the SELECT entries whose aliases a HAVING clause references, since the grouped
     * select list that count() builds would otherwise leave HAVING pointing at a missing alias.
     *
     * @since TBD
     *
     * @return string[]
     */
    private function getHavingReferencedSelects(): array
    {
        $referencedAliases = [];

        foreach ($this->havings as $having) {
            if ($having instanceof Having) {
                $referencedAliases[] = $having->column;
            }
        }

        if ( ! $referencedAliases) {
            return [];
        }

        $selects = [];

        foreach ($this->selects as $select) {
            if ($select instanceof Select) {
                if (in_array($select->alias, $referencedAliases, true)) {
                    $selects[] = DB::prepare('%1s AS %2s', $select->column, $select->alias);
                }
            } elseif ($select instanceof RawSQL) {
                if (preg_match('/\s+AS\s+([^\s,]+)\s*$/i', $select->sql, $matches) &&
                    in_array(trim($matches[1], '`\'"'), $referencedAliases, true)
                ) {
                    $selects[] = $select->sql;
                }
            }
        }

        return $selects;
    }
}
