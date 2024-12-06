<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;

/**
 * @unreleased
 */
trait InsertInto
{
    /**
     * @unreleased
     */
    public function getInsertIntoSQL($data, $format): string
    {
        $sql = 'INSERT INTO ' . $this->getTable()
               . sprintf(' (%s) ', implode(',', array_keys($data[0])))
               . 'VALUES ';

        foreach ($data as $row) {
            $sql .= DB::prepare(
                sprintf('(%s),', implode(',', $format ?? $this->getInsertIntoRowValuesFormat($row))),
                $row
            );
        }

        return rtrim($sql, ',');
    }

    /**
     * Get values format used by DB::prepare()
     *
     * @unreleased
     *
     * @param array $data
     *
     * @return array
     */
    private function getInsertIntoRowValuesFormat(array $data): array
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
