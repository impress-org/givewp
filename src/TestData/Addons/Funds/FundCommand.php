<?php

namespace Give\TestData\Addons\Funds;

use Exception;
use WP_CLI;

/**
 * Class FundCommand
 * @package Give\TestData\Funds
 *
 * A WP-CLI command for seeding funds.
 */
class FundCommand
{
    /**
     * @var FundFactory
     */
    private $fundFactory;
    /**
     * @var FundRepository
     */
    private $fundsRepository;

    /**
     * @param FundFactory    $fundFactory
     * @param FundRepository $fundsRepository
     */
    public function __construct(FundFactory $fundFactory, FundRepository $fundsRepository)
    {
        $this->fundFactory = $fundFactory;
        $this->fundsRepository = $fundsRepository;
    }

    /**
     * Generates Funds
     *
     * ## OPTIONS
     * [--count=<count>]
     * : Number of funds to generate
     * default: 5
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
     *     wp give test-funds --count=5 --preview=true
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assocArgs)
    {
        global $wpdb;
        // Get CLI args
        $count = WP_CLI\Utils\get_flag_value($assocArgs, 'count', $default = 5);
        $preview = WP_CLI\Utils\get_flag_value($assocArgs, 'preview', $default = false);
        $consistent = WP_CLI\Utils\get_flag_value($assocArgs, 'consistent', $default = null);

        $funds = $this->fundFactory->consistent($consistent)->make($count);

        if ($preview) {
            WP_CLI\Utils\format_items(
                'table',
                $funds,
                array_keys($this->fundFactory->definition())
            );
        } else {
            $progress = WP_CLI\Utils\make_progress_bar('Generating funds', $count);

            try {
                foreach ($funds as $fund) {
                    $this->fundsRepository->insertFund($fund);
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
