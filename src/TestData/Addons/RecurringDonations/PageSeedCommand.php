<?php

namespace Give\TestData\Addons\RecurringDonations;

use WP_CLI;

/**
 * Class PageSeedCommand
 * @package Give\TestData\RecurringDonations
 *
 * A WP-CLI command for seeding recurring donation's demonstration page.
 */
class PageSeedCommand
{

    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * Generates Recurring Donation's demonstration page
     *
     * [--preview=<preview>]
     * : Preview generated data
     * default: false
     *
     * ## EXAMPLES
     *
     *     wp give recurring-demonstration-page --preview=true
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assocArgs)
    {
        $preview = WP_CLI\Utils\get_flag_value($assocArgs, 'preview', $default = false);

        $page = $this->pageFactory->definition();

        if ($preview) {
            WP_CLI\Utils\format_items(
                'table',
                [$page],
                array_keys($page)
            );
        } else {
            $progress = WP_CLI\Utils\make_progress_bar('Generating Recurring Donations demonstration page', 1);

            wp_insert_post($page);
            $progress->finish();
        }
    }
}
