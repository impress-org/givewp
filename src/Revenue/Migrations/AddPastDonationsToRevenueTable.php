<?php
namespace Give\Revenue\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Revenue\Repositories\Revenue;
use Give\ValueObjects\Money;
use Give_Updates;
use InvalidArgumentException;
use WP_Query;
use Exception;

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
class AddPastDonationsToRevenueTable extends Migration {
	/**
	 * Register background update.
	 * @since 2.9.0
	 */
	public function register() {
		Give_Updates::get_instance()->register(
			[
				'id'       => self::id(),
				'version'  => '2.9.0',
				'callback' => [ $this, 'run' ],
			]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function run() {
		global $post;

		/* @var Revenue $revenueRepository */
		$revenueRepository = give( Revenue::class );
		$give_updates      = Give_Updates::get_instance();

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

				if ( $revenueRepository->isDonationExist( $post->ID ) ) {
					continue;
				}

				if ( ! ( $amount = give()->payment_meta->get_meta( $post->ID, '_give_cs_base_amount', true ) ) ) {
					$amount = give_donation_amount( $post->ID );
				}

				$revenueData = [
					'donation_id' => $post->ID,
					'form_id'     => give_get_payment_form_id( $post->ID ),
					'amount'      => Money::of( $amount, give_get_payment_currency( $post->ID ) )->getMinorAmount(),
				];

				try {
					$revenueRepository->insert( $revenueData );

				} catch ( Exception $e ) {
					give()->logs->add(
						'Update Error',
						sprintf(
							'Unable to create revenue for this data: ' . "\n" . '%1$s' . "\n" . '%2$s',
							print_r( $revenueData, true ),
							$e->getMessage()
						),
						0,
						'update'
					);

					continue;
				}
			}

			wp_reset_postdata();
			return;
		}

		// Update Ran Successfully.
		give_set_upgrade_complete( self::id() );
	}

	/**
	 * @inheritdoc
	 */
	public static function id() {
		return 'add-past-donation-data-to-revenue-table';
	}

	/**
	 * @inheritdoc
	 */
	public static function timestamp() {
		return strtotime( '2019-09-24' );
	}
}
