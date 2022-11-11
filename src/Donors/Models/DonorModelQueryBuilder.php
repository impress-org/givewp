<?php

namespace Give\Donors\Models;

use Give\Framework\Database\DB;
use Give\Framework\Models\ModelQueryBuilder;

class DonorModelQueryBuilder extends ModelQueryBuilder
{

    /**
     * Get row
     *
     * @unreleased
     *
     * @return M|null
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
     * @unreleased
     *
     * @return M[]|null
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
}
