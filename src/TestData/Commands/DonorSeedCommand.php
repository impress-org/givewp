<?php

namespace Give\TestData\Commands;

use Give\TestData\Factories\DonorFactory;
use Give\TestData\Repositories\DonorRepository;
use WP_CLI;

/**
 * Class DonorSeedCommand
 * @package Give\TestData\Commands
 *
 * A WP-CLI command for seeding donors.
 */
class DonorSeedCommand
{
    /**
     * @var DonorFactory
     */
    private $donorFactory;
    /**
     * @var DonorRepository
     */
    private $donorRepository;

    /**
     * @param DonorFactory    $donorFactory
     * @param DonorRepository $donorRepository
     */
    public function __construct(
        DonorFactory $donorFactory,
        DonorRepository $donorRepository
    ) {
        $this->donorFactory = $donorFactory;
        $this->donorRepository = $donorRepository;
    }

    /**
     * Generates Donors
     *
     * ## OPTIONS
     * [--count=<count>]
     * : Number of donors to generate
     * default: 10
     *
     * [--preview=<preview>]
     * : Preview generated data
     * default: false
     *
     * [--consistent=<consistent>]
     * : Generate consistent data
     * default: false
     *
     * ## EXAMPLES
     *
     *     wp give test-donors --count=10 --preview=true
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assocArgs)
    {
        $count = WP_CLI\Utils\get_flag_value($assocArgs, 'count', $default = 10);
        $preview = WP_CLI\Utils\get_flag_value($assocArgs, 'preview', $default = false);
        $consistent = WP_CLI\Utils\get_flag_value($assocArgs, 'consistent', $default = false);

        $donors = $this->donorFactory->consistent($consistent)->make($count);

        if ($preview) {
            WP_CLI\Utils\format_items(
                'table',
                $donors,
                array_keys($this->donorFactory->definition())
            );
        } else {
            $progress = WP_CLI\Utils\make_progress_bar('Generating donors', $count);

            foreach ($donors as $donor) {
                $this->donorRepository->insertDonor($donor);
                $progress->tick();
            }

            $progress->finish();
        }
    }
}
