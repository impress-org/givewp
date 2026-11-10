<?php

namespace Give\TestData\Repositories;

use Give\TestData\Factories\DonorFactory;
use Give\TestData\Framework\MetaRepository;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- WP-CLI test data code; it writes and reads fake data directly for speed, runs only from the command line, and is not cached.

/**
 * Class DonorRepository
 * @package GiveTestData\TestData\Repositories
 */
class DonorRepository
{
    /**
     * @var DonorFactory
     */
    private $donorFactory;

    /**
     * DonorRepository constructor.
     *
     * @param DonorFactory $donorFactory
     */
    public function __construct(DonorFactory $donorFactory)
    {
        $this->donorFactory = $donorFactory;
    }

    /**
     * Insert Donor
     *
     * @since 1.0.0
     *
     * @param array $donor
     *
     */
    public function insertDonor($donor)
    {
        global $wpdb;

        // Insert donor
        $wpdb->insert(
            "{$wpdb->prefix}give_donors",
            [
                'email' => $donor['email'],
                'name' => sprintf('%s %s', $donor['first_name'], $donor['last_name']),
                'date_created' => $donor['date_created'],
            ]
        );
        $donorID = $wpdb->insert_id;
        $metaRepository = new MetaRepository('give_donormeta', 'donor_id');

        $donorMeta = wp_parse_args(
            apply_filters('give-test-data-donor-meta', $donorID, $donor),
            [
                '_give_donor_first_name' => $donor['first_name'],
                '_give_donor_last_name' => $donor['last_name'],
            ]
        );

        $metaRepository->persist($donorID, $donorMeta);

        do_action('give-test-data-insert-donor', $donorID, $donor);
    }
}
