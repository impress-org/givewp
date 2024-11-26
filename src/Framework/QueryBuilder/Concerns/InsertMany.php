<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Clauses\Collection;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @unreleased
 */
trait InsertMany
{
    /**
     * @var Collection|null
     */
    protected $insertMany;

    /**
     * Set data
     *
     * @unreleased
     */
    public function set($data): QueryBuilder
    {
        if (is_null($this->insertMany)) {
            $this->insertMany = new Collection;
        }

        $this->insertMany->set($data);

        return $this;
    }

    /**
     * @unreleased
     */
    public function getInsertManySQL(): string
    {
        $sql = 'INSERT INTO ' . $this->getTable()
               . sprintf(' (%s) ', implode(',', $this->insertMany->getColumns()))
               . 'VALUES ';

        foreach ($this->insertMany->getData() as $data) {
            $sql .= DB::prepare(
                sprintf('(%s),', implode(',', $this->getFormats($data))),
                $data
            );
        }

        return rtrim($sql, ',');
    }

    /**
     * Get value format used by DB::prepare()
     *
     * @unreleased
     *
     * @param array $data
     *
     * @return array
     */
    private function getFormats(array $data): array
    {
        return array_map(function ($value) {
            if (is_int($value)) {
                return '%d';
            }

            if (is_float($value)) {
                return '%f';
            }

            return '%s';
        }, $data);
    }

}
