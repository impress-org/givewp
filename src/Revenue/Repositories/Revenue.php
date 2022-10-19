<?php

namespace Give\Revenue\Repositories;

use Give\Donations\Models\Donation;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class Revenue
 * @package    Give\Revenue\Repositories
 *
 * Use this class to get data from "give_revenue" table.
 *
 * @since      2.9.0
 * @since 2.22.1 Added the `updateRevenueAmount()` method
 */
class Revenue
{
    /**
     * Insert revenue.
     *
     * @since 2.9.0
     *
     * @param array $revenueData
     *
     * @return bool|int
     */
    public function insert($revenueData)
    {
        global $wpdb;

        // Validate revenue data
        $this->validateNewRevenueData($revenueData);

        /**
         * Filter revenue data before inserting to revenue table.
         *
         * @since 2.9.0
         */
        $revenueData = apply_filters('give_revenue_insert_data', $revenueData);

        return DB::insert(
            $wpdb->give_revenue,
            $revenueData,
            $this->getPlaceholderForPrepareQuery($revenueData)
        );
    }

    /**
     * Deletes revenue
     *
     * @param $revenueId
     *
     * @return false|int
     */
    public function deleteByDonationId($revenueId)
    {
        global $wpdb;

        return DB::delete(
            $wpdb->give_revenue,
            ['donation_id' => $revenueId],
            ['%d']
        );
    }

    /**
     * @since 2.22.1
     *
     * @param Donation $donation
     *
     * @return false|int
     */
    public function updateRevenueAmount(Donation $donation)
    {
        global $wpdb;

        return DB::update(
            $wpdb->give_revenue,
            ['amount' => $donation->amount->formatToMinorAmount()],
            ['donation_id' => $donation->id],
            ['%d'],
            ['%d']
        );
    }

    /**
     * Validate new revenue data.
     *
     * @since 2.9.0
     * @since 2.9.4 Mention donation id in exception message.
     *
     * @param array $array
     */
    protected function validateNewRevenueData($array)
    {
        $required = ['donation_id', 'form_id', 'amount'];

        if (empty($array['donation_id'])) {
            unset($array['donation_id']);
        }

        if (empty($array['form_id'])) {
            unset($array['form_id']);
        }

        if (!is_numeric($array['amount']) || (int)$array['amount'] < 0) {
            unset($array['amount']);
        }

        if (array_diff($required, array_keys($array))) {
            $errorMessage = '';
            if (isset($array['donation_id'])) {
                $errorMessage = "An error occurred when processing Donation #{$array['donation_id']}. ";
            }

            throw new InvalidArgumentException(
                sprintf(
                    '%2$sTo insert revenue, please provide valid %1$s.',
                    implode(', ', $required),
                    $errorMessage
                )
            );
        }
    }

    /**
     * Get placeholder for prepare query.
     *
     * @param array $data
     *
     * @return string[] Array of value format type
     */
    private function getPlaceholderForPrepareQuery($data)
    {
        $format = [];

        foreach ($data as $value) {
            $format[] = is_numeric($value) ? '%d' : '%s';
        }

        return $format;
    }

    /**
     * Return whether or not donation id exist in give_revenue table.
     *
     * @sicne 2.9.0
     *
     * @param int $donationId
     *
     * @return bool
     */
    public function isDonationExist($donationId)
    {
        global $wpdb;

        return (bool)DB::get_var(
            DB::prepare(
                "
				SELECT donation_id
				FROM {$wpdb->give_revenue}
				WHERE donation_id = %d
				",
                $donationId
            )
        );
    }
}
