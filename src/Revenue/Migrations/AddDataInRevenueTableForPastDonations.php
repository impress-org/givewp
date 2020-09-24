<?php
namespace Give\Revenue\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give_Updates;
use WP_Query;

/**
 * Class AddDataInRevenueTableForPastDonations
 * @package Give\Revenue\Migrations
 *
 * @since 2.9.0
 */
class AddDataInRevenueTableForPastDonations extends Migration {
	/**
	 * @inheritdoc
	 */
	public function run() {
		$give_updates = Give_Updates::get_instance();

		$donations = new WP_Query(
			[
				'paged'          => $give_updates->step,
				'status'         => 'any',
				'order'          => 'ASC',
				'post_type'      => [ 'give_payment' ],
				'posts_per_page' => 100,
			]
		);

		if ( $donations->have_posts() ) {
			$give_updates->set_percentage( $donations->found_posts, $give_updates->step * 100 );

			while ( $donations->have_posts() ) {
				$donations->the_post();
				$donationId = get_the_ID();
			}

			wp_reset_postdata();
		} else {
			// Update Ran Successfully.
			give_set_upgrade_complete( 'v270_store_stripe_account_for_donation' );
		}
	}

	/**
	 * @inheritdoc
	 */
	public static function id() {
		return 'add-data-to-revenue-table-for-past-donations';
	}

	/**
	 * @inheritdoc
	 */
	public static function timestamp() {
		return strtotime( '2020-09-23' );
	}
}
