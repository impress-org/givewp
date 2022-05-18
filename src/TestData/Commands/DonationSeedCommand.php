<?php

namespace Give\TestData\Commands;

use Exception;
use Give\TestData\Factories\DonationFactory as DonationFactory;
use Give\TestData\Repositories\DonationRepository as DonationRepository;
use WP_CLI;

/**
 * Class DonationSeedCommand
 * @package Give\TestData\Commands
 *
 * A WP-CLI command for seeding donations.
 */
class DonationSeedCommand
{
    /**
     * @var DonationFactory
     */
    private $donationFactory;
    /**
     * @var DonationRepository
     */
    private $donationRepository;

    /**
     * @param DonationFactory    $donationFactory
     * @param DonationRepository $donationRepository
     */
    public function __construct(
        DonationFactory $donationFactory,
        DonationRepository $donationRepository
    ) {
        $this->donationFactory = $donationFactory;
        $this->donationRepository = $donationRepository;
    }

    /**
     * Generates Donations
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of donations to generate
     * default: 10
     *
     * [--status=<status>]
     * : Donation status
     * default: publish
     * options:
     *   - publish
     *   - random
     * get all available statuses with command:
     *     wp give test-donation-statuses
     *
     * [--total-revenue=<amount>]
     * : Total revenue amount to be generated
     * default: 0
     *
     * [--preview=<preview>]
     * : Preview generated data
     * default: false
     *
     * [--start-date=<date>]
     * : Set donation start date. Date format is YYYY-MM-DD
     * default: false
     *
     * [--params=<params>]
     * : Additional params
     * default: ''
     *
     * [--consistent=<consistent>]
     * : Generate consistent data
     * default: false
     *
     * ## EXAMPLES
     *
     *     wp give test-donations --count=50 --status=random --total-revenue=10000 --start-date=2020-11-22 --params=donation_currency=EUR
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assocArgs)
    {
        global $wpdb;
        // Get CLI args
        $count = WP_CLI\Utils\get_flag_value($assocArgs, 'count', $default = 10);
        $preview = WP_CLI\Utils\get_flag_value($assocArgs, 'preview', $default = false);
        $status = WP_CLI\Utils\get_flag_value($assocArgs, 'status', $default = 'publish');
        $totalRevenue = WP_CLI\Utils\get_flag_value($assocArgs, 'total-revenue', $default = 0);
        $startDate = WP_CLI\Utils\get_flag_value($assocArgs, 'start-date', $default = false);
        $additional = WP_CLI\Utils\get_flag_value($assocArgs, 'params', $default = '');
        $consistent = WP_CLI\Utils\get_flag_value($assocArgs, 'consistent', $default = false);

        // Additional params
        parse_str($additional, $params);

        try {
            // Factory config
            $this->donationFactory->setDonationStatus($status);

            if ($totalRevenue) {
                $this->donationFactory->setDonationAmount(($totalRevenue / $count));
            }

            if ($startDate) {
                $this->donationFactory->setDonationStartDate($startDate);
            }

            // Generate donations
            $donations = $this->donationFactory->consistent($consistent)->make($count);
        } catch (Exception $e) {
            return WP_CLI::error($e->getMessage());
        }

        if ($preview) {
            WP_CLI\Utils\format_items(
                'table',
                $donations,
                array_keys($this->donationFactory->definition())
            );
        } else {
            $progress = WP_CLI\Utils\make_progress_bar('Generating donations', $count);

            // Start DB transaction
            $wpdb->query('START TRANSACTION');

            try {
                foreach ($donations as $donation) {
                    $this->donationRepository->insertDonation($donation, $params);
                    $progress->tick();
                }

                $wpdb->query('COMMIT');

                $progress->finish();
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');

                WP_CLI::error($e->getMessage());
            }
        }
    }
}
