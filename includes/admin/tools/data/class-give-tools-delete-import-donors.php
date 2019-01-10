<?php
/**
 * Delete Donors.
 *
 * This class handles batch processing of deleting donor data.
 *
 * @subpackage  Admin/Tools/Give_Tools_Delete_Donors
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.12
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Give_Tools_Import_Donors Class
 *
 * @since 1.8.13
 */
class Give_Tools_Import_Donors extends Give_Batch_Export {

	/**
	 * Form Data passed in batch processing.
	 *
	 * @var $request
	 */
	var $request;

	/**
	 * Used to store form id's that are going to get recount.
	 *
	 * @var $form_key
	 *
	 * @since 1.8.13
	 */
	var $form_key = 'give_temp_delete_form_ids';

	/**
	 * Used to store donation id's that are going to get deleted.
	 *
	 * @var $donation_key
	 *
	 * @since 1.8.12
	 */
	var $donation_key = 'give_temp_delete_donation_ids';

	/**
	 * Used to store the step where the step will be. ( 'count', 'donations', 'donors' ).
	 *
	 * @var $step_key
	 *
	 * @since 1.8.12
	 */
	var $step_key = 'give_temp_delete_step';

	/**
	 * Used to store donors id's that are going to get deleted.
	 *
	 * @var $donor_key
	 *
	 * @since 1.8.12
	 */
	var $donor_key = 'give_temp_delete_donor_ids';

	/**
	 * Used to store to get the page count in the loop.
	 *
	 * @var $step_on_key
	 *
	 * @since 1.8.12
	 */
	var $step_on_key = 'give_temp_delete_step_on';

	/**
	 * Contain total number of step.
	 *
	 * @var $total_step
	 *
	 * @since 1.8.12
	 */
	var $total_step;

	/**
	 * Counting contain total number of step that completed.
	 *
	 * @var $step_completed
	 *
	 * @since 1.8.12
	 */
	var $step_completed;

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var $export_type
	 *
	 * @since 1.8.12
	 */
	public $export_type = '';

	/**
	 * Allows for a non-form batch processing to be run.
	 *
	 * @var $is_void
	 *
	 * @since 1.8.12
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 *
	 * @var $per_step
	 *
	 * @since 1.8.12
	 */
	public $per_step = 10;

	/**
	 * Set's all the donors id's
	 *
	 * @var $donor_ids
	 *
	 * @since 1.8.12
	 */
	public $donor_ids = array();

	/**
	 * Give_Tools_Import_Donors constructor.
	 *
	 * @param int $_step Steps.
	 */
	public function __construct( $_step = 1 ) {
		parent::__construct( $_step );

		$this->is_writable = true;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.8.12
	 * @global object $wpdb Used to query the database using the WordPress Database API
	 *
	 * @return void
	 */
	public function pre_fetch() {
		$donation_ids = array();
		$donor_ids    = array();

		// Check if the ajax request if running for the first time.
		if ( 1 === (int) $this->step ) {

			// Delete all the form ids.
			$this->delete_option( $this->form_key );

			// Delete all the donation ids.
			$this->delete_option( $this->donation_key );

			// Delete all the donor ids.
			$this->delete_option( $this->donor_key );

			// Delete all the step and set to 'count' which if the first step in the process of deleting the donors.
			$this->update_option( $this->step_key, 'count' );

			// Delete tha page count of the step.
			$this->update_option( $this->step_on_key, '0' );
		} else {

			// Get the old donors list.
			$donor_ids = $this->get_option( $this->donor_key );

			// Get the old donation list.
			$donation_ids = $this->get_option( $this->donation_key );
		}

		// Get the step and check for it if it's on the first step( 'count' ) or not.
		$step = (int) $this->get_step();
		if ( 1 === $step ) {
			/**
			 * Will add or update the donation and donor data by running wp query.
			 */
			$this->count( $step, $donation_ids, $donor_ids );
		}
	}

	/**
	 * Will Update or Add the donation and donors ids in the with option table for there respected key.
	 *
	 * @param string $step         On which the current ajax is running.
	 * @param array  $donation_ids Contain the list of all the donation id's that has being add before this.
	 * @param array  $donor_ids    Contain the list of all the donors id's that has being add before this.
	 */
	private function count( $step, $donation_ids = array(), $donor_ids = array() ) {

		// Get the Page count by default it's zero.
		$paged = (int) $this->get_step_page();

		// Increased the page count by one.
		++ $paged;

		/**
		 * Filter add to alter the argument before the wp quest run
		 */
		$args = apply_filters( 'give_tools_reset_stats_total_args', array(
			'post_type'      => 'give_payment',
			'post_status'    => 'any',
			'posts_per_page' => $this->per_step,
			'paged'          => $paged,
			'meta_key'       => '_give_payment_import',
			'meta_value_num' => 1,
			'meta_compare'   => '=',
		) );

		// Reset the post data.
		wp_reset_postdata();

		// Getting the new donation.
		$donation_posts = new WP_Query( $args );

		// The Loop.
		if ( $donation_posts->have_posts() ) {
			while ( $donation_posts->have_posts() ) {
				$add_author = true;
				$donation_posts->the_post();
				global $post;
				// Add the donation id in side the array.
				$donation_ids[] = $post->ID;

				$donor_id = (int) give_get_meta( $post->ID, '_give_payment_customer_id', true );
				if ( ! empty( $donor_id ) ) {
					$donor = new Give_Donor( $donor_id );
					if ( ! empty( $donor->id ) ) {
						if ( empty( $donor->user_id ) && ! empty( $donor->payment_ids ) ) {
							$add_author = false;
							$count      = (int) count( $donor->payment_ids );
							if ( 1 === $count ) {
								give_delete_donor_and_related_donation( $donor );
							} else {
								$donor->remove_payment( $post->ID );
								$donor->decrease_donation_count();
							}
						}
					}
				}

				if ( ! empty( $add_author ) ) {
					// Add the donor id in side the array.
					$donor_ids[] = (int) $post->post_author;
				}
			}
		}

		// Get the total number of post found.
		$total_donation = (int) $donation_posts->found_posts;

		// Maximum number of page can be display.
		$max_num_pages = (int) $donation_posts->max_num_pages;

		// Check current page is less then max number of page or not.
		if ( $paged < $max_num_pages ) {

			// Update the current page variable for the next step.
			$this->update_option( $this->step_on_key, $paged );

			// Calculating percentage.
			$page_remain          = $max_num_pages - $paged;
			$this->total_step     = (int) $max_num_pages + ( $total_donation / $this->per_step ) + ( ( $page_remain * 2 ) * count( $donor_ids ) );
			$this->step_completed = $paged;
		} else {
			$donation_ids_count = count( $donor_ids );
			$this->update_option( $this->step_key, 'donation' );
			$this->update_option( $this->step_on_key, '0' );
		}

		$donor_ids = array_unique( $donor_ids );
		$this->update_option( $this->donor_key, $donor_ids );
		$this->update_option( $this->donation_key, $donation_ids );

		wp_reset_postdata();
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 1.8.12
	 *
	 * @return int
	 */
	public function get_percentage_complete() {
		return ceil( ( 100 * $this->step_completed ) / $this->total_step );
	}

	/**
	 * Process Steps
	 *
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die(
				esc_html__( 'You do not have permission to delete Import transactions.', 'give' ),
				esc_html__( 'Error', 'give' ),
				array(
					'response' => 403,
				)
			);
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			update_option( 'give_earnings_total', give_get_total_earnings( true ), false );
			Give_Cache::delete( Give_Cache::get_key( 'give_estimated_monthly_stats' ) );

			$this->delete_option( $this->donation_key );

			$this->done    = true;
			$this->message = __( 'Imported donor and transactions successfully deleted.', 'give' );

			return false;
		}
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.8.12
	 * @global object $wpdb Used to query the database using the WordPress Database API
	 *
	 * @return array|bool $data The data for the CSV file
	 */
	public function get_data() {

		// Get the donation id's.
		$donation_ids = $this->get_option( $this->donation_key );

		/**
		 * Return false id not Import donation is found.
		 */
		if ( empty( $donation_ids ) ) {
			$this->is_empty   = true;
			$this->total_step = 1;

			return false;
		}

		// Get the current step.
		$step = (int) $this->get_step();

		// Get the donor ids.
		$donor_ids = $this->get_option( $this->donor_key );

		// Delete all the imported donations.
		if ( 2 === $step ) {
			$pass_to_donor = false;
			$page          = (int) $this->get_step_page();
			$page ++;
			$count = count( $donation_ids );

			$this->total_step     = ( ( count( $donation_ids ) / $this->per_step ) * 2 ) + count( $donor_ids );
			$this->step_completed = $page;

			if ( $count > $this->per_step ) {

				$this->update_option( $this->step_on_key, $page );
				$donation_ids = $this->get_delete_ids( $donation_ids, $page );
				$current_page = (int) ceil( $count / $this->per_step );

				if ( $page === $current_page ) {
					$pass_to_donor = true;
				}
			} else {
				$pass_to_donor = true;
			}

			if ( true === $pass_to_donor ) {
				$this->update_option( $this->step_key, 'donor' );
				$this->update_option( $this->step_on_key, '0' );
			}

			// Get the old form list.
			$form_ids = (array) $this->get_option( $this->form_key );

			foreach ( $donation_ids as $item ) {
				$form_ids[] = give_get_meta( $item, '_give_payment_form_id', true );

				// Delete the main payment.
				give_delete_donation( absint( $item ) );
			}

			// Update the new form list.
			$this->update_option( $this->form_key, $form_ids );
		} // End if().

		// Delete all the donors.
		if ( 3 === $step ) {

			// Get the old form list.
			$form_ids = (array) $this->get_option( $this->form_key );
			if ( ! empty( $form_ids ) ) {
				$form_ids = array_unique( $form_ids );
				foreach ( $form_ids as $form_id ) {
					give_recount_form_income_donation( (int) $form_id );
				}
			}
			// update the new form list.
			$this->update_option( $this->form_key, array() );

			$page  = (int) $this->get_step_page();
			$count = count( $donor_ids );

			$this->total_step     = ( ( count( $donation_ids ) / $this->per_step ) * 2 ) + count( $donor_ids );
			$this->step_completed = $page + ( count( $donation_ids ) / $this->per_step );

			if ( ! empty( $donor_ids[ $page ] ) ) {
				$args = apply_filters( 'give_tools_reset_stats_total_args', array(
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'author'         => $donor_ids[ $page ],
				) );

				$donations = array();
				$payments  = new Give_Payments_Query( $args );
				$payments  = $payments->get_payments();
				if ( empty( $payments ) ) {
					Give()->donors->delete_by_user_id( $donor_ids[ $page ] );

					/**
					 * If Checked then delete WP user.
					 *
					 * @since 1.8.14
					 */
					$delete_import_donors = isset( $_REQUEST['delete-import-donors'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['delete-import-donors'] ) ) : '';

					if ( 'on' === (string) $delete_import_donors ) {
						wp_delete_user( $donor_ids[ $page ] );
					}
				} else {
					foreach ( $payments as $payment ) {
						$donations[] = $payment->ID;
					}

					$donor          = new Give_Donor( $donor_ids[ $page ], true );
					$data_to_update = array(
						'purchase_count' => count( $donations ),
						'payment_ids'    => implode( ',', $donations ),
					);
					$donor->update( $data_to_update );
				}
			} // End if().

			$page ++;
			$this->update_option( $this->step_on_key, $page );
			if ( $count === $page ) {
				$this->is_empty = false;

				return false;
			}

			return true;
		} // End if().

		return true;
	}

	/**
	 * This function will get list of donation ids ready for deletion.
	 *
	 * @param array  $donation_ids List of donation ids.
	 * @param string $page         Ajax on Page.
	 *
	 * @return mixed
	 */
	public function get_delete_ids( $donation_ids, $page ) {
		$index            = $page --;
		$count            = count( $donation_ids );
		$temp             = 0;
		$current_page     = 0;
		$post_delete      = $this->per_step;
		$page_donation_id = array();

		foreach ( $donation_ids as $item ) {
			$temp ++;
			$page_donation_id[ $current_page ][] = $item;
			if ( $temp === $post_delete ) {
				$current_page ++;
				$temp = 0;
			}
		}

		return $page_donation_id[ $page ];
	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since 1.8.12
	 *
	 * @param string $key           Option Key.
	 * @param bool   $default_value True, if default value, else false.
	 *
	 * @return mixed Returns the data from the database
	 */
	public function get_option( $key, $default_value = false ) {
		return get_option( $key, $default_value );
	}

	/**
	 * Give a key, store the value
	 *
	 * @since 1.8.12
	 *
	 * @param string $key   Option Key.
	 * @param mixed  $value Option Value.
	 *
	 * @return void
	 */
	public function update_option( $key, $value ) {
		update_option( $key, $value, false );
	}

	/**
	 * Delete an option
	 *
	 * @since 1.8.12
	 *
	 * @param string $key Option Key.
	 *
	 * @return void
	 */
	public function delete_option( $key ) {
		delete_option( $key );
	}

	/**
	 * Get the current step in number.
	 *
	 * There are three step to delete the total donor first counting, second deleting donotion and third deleting donors.
	 *
	 * @return int|string
	 */
	private function get_step() {
		$step_key = (string) $this->get_option( $this->step_key, false );
		if ( 'count' === $step_key ) {
			return 1;
		} elseif ( 'donation' === $step_key ) {
			return 2;
		} elseif ( 'donor' === $step_key ) {
			return 3;
		} else {
			return $step_key;
		}
	}

	/**
	 * Get the current $page value in the ajax.
	 */
	private function get_step_page() {
		return $this->get_option( $this->step_on_key, false );
	}

	/**
	 * Unset the properties specific to the donors export.
	 *
	 * @since 2.3.0
	 *
	 * @param array $request
	 * @param Give_Batch_Export $export
	 */
	public function unset_properties( $request, $export ) {
		if ( $export->done ) {
			// Delete all the form ids.
			$this->delete_option( $this->form_key );

			// Delete all the donation ids.
			$this->delete_option( $this->donation_key );

			// Delete all the donor ids.
			$this->delete_option( $this->donor_key );

			// Delete all the step and set to 'count' which if the first step in the process of deleting the donors.
			$this->delete_option( $this->step_key );

			// Delete tha page count of the step.
			$this->delete_option( $this->step_on_key );
		}
	}
}
