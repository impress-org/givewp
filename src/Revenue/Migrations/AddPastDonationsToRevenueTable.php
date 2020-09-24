<?php
namespace Give\Revenue\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Revenue\Repositories\Revenue;
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
				'id'       => self::timestamp() . self::id(),
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

				$revenueData = [
					'donation_id' => $post->ID,
					'form_id'     => give_get_payment_form_id( $post->ID ),
					'amount'      => give_donation_amount( $post->ID ) * 100,
				];

				try {
					$this->validateRevenueData( $revenueData );

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

				$revenueRepository->insert( $revenueData );
			}

			wp_reset_postdata();
			return;
		}

		// Update Ran Successfully.
		give_set_upgrade_complete( self::timestamp() . self::id() );
	}

	/**
	 * @inheritdoc
	 */
	public static function id() {
		return 'add-past-donation-data-to-revenue-table-3';
	}

	/**
	 * @inheritdoc
	 */
	public static function timestamp() {
		return strtotime( '2019-09-24' );
	}

	/**
	 * Validate revenue data.
	 *
	 * @sicne 2.9.0
	 *
	 * @param array  $array
	 * @throws InvalidArgumentException
	 */
	private function validateRevenueData( $array ) {
		foreach ( $array as $value ) {
			if ( empty( $value ) ) {
				throw new InvalidArgumentException( 'Empty value is not allowed to create revenue.' );
			}
		}
	}
}
