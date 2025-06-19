<?php

namespace Give\Donors\Models;

use Give\Donors\ValueObjects\DonorAddress;
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
        $row = $this->attachAddresses($row);

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
        $results = $this->attachAddresses($results);

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

    /**
     * Attach addresses to query results later so that we can avoid additional Group By on the main query
     *
     * @since 4.4.0
     *
     * @param array|object $queryResults
     *
     * @return array|object
     */
    private function attachAddresses($queryResults)
    {
        if (is_array($queryResults)) {
            $donorIds = wp_list_pluck($queryResults, 'id');
            $addresses = $this->getAddresses($donorIds);
            $queryResults = array_map(function ($donor) use ($addresses) {
                $donor->addresses = $addresses[$donor->id] ?? [];

                return $donor;
            }, $queryResults);
        } else {
            $queryResults->addresses = $this->getAddresses([$queryResults->id], true) ?? [];
        }

        return $queryResults;
    }

    /**
     * @since 4.4.0
     *
     * @param array $donorIds Array of donor ids
     * @param bool  $single Return addresses for the first donor id
     *
     * @return array|null
     */
    private function getAddresses(array $donorIds, bool $single = false)
    {
        // Get all address-related meta for the donors
        $results = DB::table('give_donormeta')
            ->select(['donor_id', 'donorId'], ['meta_key', 'metaKey'], ['meta_value', 'metaValue'])
            ->whereIn('donor_id', $donorIds)
            ->where(function($query) {
                $query->where('meta_key', '_give_donor_address_billing_line1_%', 'LIKE')
                      ->orWhere('meta_key', '_give_donor_address_billing_line2_%', 'LIKE')
                      ->orWhere('meta_key', '_give_donor_address_billing_city_%', 'LIKE')
                      ->orWhere('meta_key', '_give_donor_address_billing_state_%', 'LIKE')
                      ->orWhere('meta_key', '_give_donor_address_billing_country_%', 'LIKE')
                      ->orWhere('meta_key', '_give_donor_address_billing_zip_%', 'LIKE');
            })
            ->getAll();

        if (!$results) {
            return null;
        }

        // Group by donor and index to build addresses
        $addressData = [];
        foreach ($results as $result) {
            $donorId = (int)$result->donorId;

            // Extract the address field type and index from the meta key
            if (preg_match('/_give_donor_address_billing_(.+)_(\d+)$/', $result->metaKey, $matches)) {
                $field = $matches[1];
                $index = (int)$matches[2];

                if (!isset($addressData[$donorId][$index])) {
                    $addressData[$donorId][$index] = [];
                }

                // Map field names to Address property names
                $fieldMap = [
                    'line1' => 'address1',
                    'line2' => 'address2',
                    'city' => 'city',
                    'state' => 'state',
                    'country' => 'country',
                    'zip' => 'zip'
                ];

                if (isset($fieldMap[$field])) {
                    $addressData[$donorId][$index][$fieldMap[$field]] = $result->metaValue;
                }
            }
        }

        // Convert to Address objects
        $addresses = [];
        foreach ($addressData as $donorId => $donorAddresses) {
            $addresses[$donorId] = [];
            foreach ($donorAddresses as $addressArray) {
                // Only create address if we have at least one field
                if (!empty(array_filter($addressArray))) {
                    $addresses[$donorId][] = DonorAddress::fromArray($addressArray);
                }
            }
        }

        if ($single) {
            return $addresses[$donorIds[0]] ?? [];
        }

        return $addresses;
    }
}
