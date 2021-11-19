<?php

namespace Give\Revenue\Migrations;

use Exception;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Log\Log;
use Give\Revenue\Repositories\Revenue;
use Give\ValueObjects\Money;
use Give_Updates;
use WP_Query;

/**
 * Class AddPastDonationToRevenueTable
 *
 * Use this table to migrated past donations data to revenue table.
 * This data migration will perform in background.
 *
 * @package Give\Revenue\Migrations
 *
 * @since 2.9.0
 */
class AddPastDonationsToRevenueTable extends Migration
{
    /**
     * Register background update.
     *
     * @since 2.9.0
     *
     * @param Give_Updates $give_updates
     *
     */
    public function register($give_updates)
    {
        $give_updates->register(
            [
                'id' => self::id(),
                'version' => '2.9.0',
                'callback' => [$this, 'run'],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        global $post;

        /* @var Revenue $revenueRepository */
        $revenueRepository = give(Revenue::class);
        $give_updates = Give_Updates::get_instance();

        $donations = new WP_Query(
            [
                'paged' => $give_updates->step,
                'status' => 'any',
                'order' => 'ASC',
                'post_type' => ['give_payment'],
                'posts_per_page' => 100,
            ]
        );

        if ($donations->have_posts()) {
            $give_updates->set_percentage($donations->found_posts, $give_updates->step * 100);

            while ($donations->have_posts()) {
                $donations->the_post();

                if ($revenueRepository->isDonationExist($post->ID)) {
                    continue;
                }

                if ( ! ($amount = give()->payment_meta->get_meta($post->ID, '_give_cs_base_amount', true))) {
                    $amount = give_donation_amount($post->ID);
                }

                $revenueData = [
                    'donation_id' => $post->ID,
                    'form_id' => give_get_payment_form_id($post->ID),
                    'amount' => Money::of($amount, give_get_option('currency'))->getMinorAmount(),
                ];

                try {
                    $revenueRepository->insert($revenueData);
                } catch (Exception $e) {
                    $give_updates->__pause_db_update(true);
                    update_option('give_upgrade_error', 1, false);

                    Log::error(
                        esc_html__('An error occurred inserting data into the revenue table', 'give'),
                        [
                            'source' => 'Revenue Migration',
                            'Data' => $revenueData,
                            'Error' => $e->getMessage(),
                        ]
                    );

                    wp_die();
                }
            }

            wp_reset_postdata();
        } else {
            // Update Ran Successfully.
            give_set_upgrade_complete(self::id());
        }
    }

    /**
     * @inheritdoc
     */
    public static function id()
    {
        return 'add-past-donation-data-to-revenue-table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2019-09-24');
    }
}
