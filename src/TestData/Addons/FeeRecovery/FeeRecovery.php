<?php

namespace Give\TestData\Addons\FeeRecovery;

use Exception;
use Give\TestData\Framework\MetaRepository;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WP-CLI test data code; it writes and reads fake data directly for speed, runs only from the command line, and is not cached.

class FeeRecovery
{
    /**
     * @param int $donationID
     * @param array $donation
     * @param array $params
     */
    public function addFee($donationID, $donation, $params)
    {
        global $wpdb;

        // Fee recovery is checked?
        if (
            ! isset($params['donation_cover_fees'])
            || ! filter_var($params['donation_cover_fees'], FILTER_VALIDATE_BOOLEAN)
        ) {
            return;
        }

        // Start DB transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Update donation meta
            $metaRepository = new MetaRepository('give_donationmeta', 'donation_id');
            $metaRepository->persist(
                $donationID,
                [
                    '_give_fee_donation_amount' => $donation['payment_total'],
                    '_give_fee_amount' => give_get_option('give_fee_percentage', 2.90),
                ]
            );

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }
    }

}
