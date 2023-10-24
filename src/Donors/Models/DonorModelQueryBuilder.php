<?php

namespace Give\Donors\Models;

use Give\Framework\Database\DB;
use Give\Framework\Models\ModelQueryBuilder;

class DonorModelQueryBuilder extends ModelQueryBuilder
{

    /**
     * Get row
     *
     * @since 2.24.0
     *
     * @return M|null
     *
     * @param int $output For inheritance compatibility only, unused.
     */
    public function get($output = OBJECT)
    {
        $row = DB::get_row($this->getSQL(), OBJECT);

        if ( ! $row) {
            return null;
        }

        $row = $this->attachAdditionalEmails($row);

        return $this->getRowAsModel($row);
    }

    /**
     * Get results
     *
     * @since 2.24.0
     *
     * @return M[]|null
     *
     * @param int $output For inheritance compatibility only, unused.
     */
    public function getAll($output = OBJECT)
    {
        $results = DB::get_results($this->getSQL(), OBJECT);

        if ( ! $results) {
            return null;
        }

        $results = $this->attachAdditionalEmails($results);

        if (isset($this->model)) {
            return $this->getAllAsModel($results);
        }

        return $results;
    }

    /**
     * Attach additional emails to query results later so that we can avoid additional Group By on the main query
     *
     * @since 2.24.0
     *
     * @param array|object $queryResults
     *
     * @return array|object
     */
    private function attachAdditionalEmails($queryResults)
    {
        if (is_array($queryResults)) {
            $donorIds = wp_list_pluck($queryResults, 'id');
            $additionalEmails = $this->getAdditionalEmails($donorIds);
            $queryResults = array_map(function ($donor) use ($additionalEmails) {
                $donor->additionalEmails = $additionalEmails[$donor->id] ?? [];

                return $donor;
            }, $queryResults);
        } else {
            $queryResults->additionalEmails = $this->getAdditionalEmails([$queryResults->id], true) ?? [];
        }

        return $queryResults;
    }

    /**
     * @since 2.24.0
     *
     * @param array $donorIds Array of donor ids
     * @param bool  $single Return additional emails for the first donor id
     *
     * @return array|null
     */
    private function getAdditionalEmails(array $donorIds, bool $single = false)
    {
        $results = DB::table('give_donormeta')
            ->select(['donor_id', 'donorId'], ['meta_value', 'additionalEmails'])
            ->whereIn('donor_id', $donorIds)
            ->where('meta_key', 'additional_email')
            ->getAll();

        if (!$results) {
            return null;
        }

        $additionalEmails = [];
        foreach ($results as $result) {
            $additionalEmails[(int)$result->donorId][] = $result->additionalEmails;
        }

        if ($single) {
            return $additionalEmails[$donorIds[0]];
        }

        return $additionalEmails;
    }
}
