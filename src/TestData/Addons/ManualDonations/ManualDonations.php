<?php

namespace Give\TestData\Addons\ManualDonations;

use Exception;
use Give\TestData\Framework\MetaRepository;

/**
 * Class ManualDonations
 * @package Give\TestData\ManualDonations
 *
 * @since 2.15.0 update class code to maintain PHP 5.6 compatibility
 */
class ManualDonations
{

    const GATEWAY = 'manual_donation';

    /**
     * @param int   $donationID
     * @param array $donation
     */
    public function updateDonationMeta($donationID, $donation)
    {
        global $wpdb;

        // Check gateway
        if ($donation['payment_gateway'] !== self::GATEWAY) {
            return;
        }

        // Start DB transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Update donation meta
            $metaRepository = new MetaRepository('give_donationmeta', 'donation_id');
            $metaRepository->persist($donationID, ['_give_manually_added_donation' => 1]);

            $wpdb->query('COMMIT');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }
    }

}
