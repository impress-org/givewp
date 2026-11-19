<?php

namespace Give\Donations\Storage;

use WP_CLI;

/**
 * wp give check-donation-storage
 *
 * Runs the drift check now and prints the report, so support can confirm the two storages agree
 * without waiting for the daily run.
 *
 * @since TBD
 */
class DonationStorageDriftCommand
{
    /**
     * @var DonationStorageDriftCheck
     */
    private $check;

    /**
     * @since TBD
     */
    public function __construct(DonationStorageDriftCheck $check)
    {
        $this->check = $check;
    }

    /**
     * @since TBD
     */
    public function __invoke()
    {
        if (DonationStorageFlag::get()->isPosts()) {
            WP_CLI::error('The give_donations table is not in use on this site.');
        }

        $report = $this->check->run();

        WP_CLI::line(json_encode($report, JSON_PRETTY_PRINT));

        $report['drift']
            ? WP_CLI::warning('The posts storage and the give_donations table disagree.')
            : WP_CLI::success('The posts storage and the give_donations table agree.');
    }
}
