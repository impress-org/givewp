<?php
/**
 * Delete Donors.
 *
 * This class handles batch processing of deleting donor data.
 *
 * @subpackage  Admin/Tools/Give_Tools_Delete_Donors
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.12
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Give_Tools_Delete_Donors Class
 *
 * @since 1.8.12
 */
class Give_Tools_Delete_Donors extends Give_Batch_Export {

	var $request;

	var $donation_key = 'give_temp_delete_donation_ids';
	var $donor_key = 'give_temp_delete_donor_ids';
	var $step_key = 'give_temp_delete_step';
	var $step_on_key = 'give_temp_delete_step_on';
	var $total_step;
	var $step_completed;

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.8.12
	 */
	public $export_type = '';

	/**
	 * Allows for a non-form batch processing to be run.
	 * @since  1.8.12
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  1.8.12
	 * @var integer
	 */
	public $per_step = 10;

	public $donor_ids = array();

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.8.12
	 * @global object $wpdb Used to query the database using the WordPress Database API
	 *
	 * @return array|bool $data The data for the CSV file
	 */
	public function pre_fetch() {
		$donation_ids = array();
		$donor_ids    = array();
		if ( 1 === (int) $this->step ) {
			$this->delete_option( $this->donation_key );
			$this->delete_option( $this->donor_key );
			$this->update_option( $this->step_key, 'count' );
			$this->update_option( $this->step_on_key, '0' );
		} else {
			$donor_ids    = $this->get_option( $this->donor_key );
			$donation_ids = $this->get_option( $this->donation_key );
		}

		$step = (int) $this->get_step();
		if ( 1 === $step ) {
			$this->count( $step, $donation_ids, $donor_ids );
		}
	}

	private function count( $step, $donation_ids = array(), $donor_ids = array() ) {

		$paged = (int) $this->get_step_page();
		++ $paged;

		$args = apply_filters( 'give_tools_reset_stats_total_args', array(
			'post_type'      => 'give_payment',
			'post_status'    => 'any',
			'posts_per_page' => $this->per_step,
			'paged'          => $paged,
			// ONLY TEST MODE TRANSACTIONS!!!
			'meta_key'       => '_give_payment_mode',
			'meta_value'     => 'test'
		) );


		wp_reset_postdata();
		$donation_posts = new WP_Query( $args );
		// The Loop.
		if ( $donation_posts->have_posts() ) {
			while ( $donation_posts->have_posts() ) {
				$donation_posts->the_post();
				global $post;
				$donation_ids[] = $post->ID;

				$donor_ids[] = (int) $post->post_author;
			}
			/* Restore original Post Data */
		}

		$total_donation = (int) $donation_posts->found_posts;
		$max_num_pages  = (int) $donation_posts->max_num_pages;

		if ( $paged < $max_num_pages ) {
			$this->update_option( $this->step_on_key, $paged );

			$page_remain = $max_num_pages - $paged;

			$this->total_step = (int) $max_num_pages + ( $total_donation / $this->per_step ) + ( ( $page_remain * 2 ) * count( $donor_ids ) );
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
	 * @return int
	 */
	public function get_percentage_complete() {
		return  ceil( ( 100 * $this->step_completed ) / $this->total_step );
	}

	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to delete test transactions.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			update_option( 'give_earnings_total', give_get_total_earnings( true ) );
			Give_Cache::delete( Give_Cache::get_key( 'give_estimated_monthly_stats' ) );

			$this->delete_option( $this->donation_key );

			// Reset the sequential order numbers
			if ( give_get_option( 'enable_sequential' ) ) {
				delete_option( 'give_last_payment_number' );
			}

			$this->done    = true;
			$this->message = __( 'Test donor and transactions successfully deleted.', 'give' );

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

		$donation_ids = $this->get_option( $this->donation_key );

		/**
		 * Return false id not test donation is found.
		 */
		if ( empty( $donation_ids ) ) {
			$this->is_empty = true;
			$this->total_step = 1;
			return false;
		}

		$step = (int) $this->get_step();
		$donor_ids = $this->get_option( $this->donor_key );

		// In step to we delete all the donation in loop.
		if ( 2 === $step ) {


			$pass_to_donor = false;
			$page          = (int) $this->get_step_page();
			$page ++;
			$count = count( $donation_ids );

			$this->total_step = ( count( $donation_ids ) / $this->per_step ) + count( $donor_ids );
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

			foreach ( $donation_ids as $item ) {
				wp_delete_post( $item, true );
			}
		}


		// Here we delete all the donor
		if ( 3 === $step ) {
			$page      = (int) $this->get_step_page();
			$count     = count( $donor_ids );

			$this->total_step = ( count( $donation_ids ) / $this->per_step ) + count( $donor_ids );
			$this->step_completed = $page + ( count( $donation_ids ) / $this->per_step );

			$args = apply_filters( 'give_tools_reset_stats_total_args', array(
				'post_type'      => 'give_payment',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => '_give_payment_mode',
				'meta_value'     => 'live',
				'author'         => $donor_ids[ $page ]
			) );

			$donation_posts = get_posts( $args );
			if ( empty( $donation_posts ) ) {
				Give()->donors->delete_by_user_id( $donor_ids[ $page ] );
			}

			$page ++;
			$this->update_option( $this->step_on_key, $page );
			if ( $count === $page ) {
				$this->is_empty = false;

				return false;
			}

			return true;
		}

		return true;
	}

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
	 * @since  1.8.12
	 *
	 * @param  string $key The option_name
	 *
	 * @return mixed       Returns the data from the database
	 */
	public function get_option( $key, $defalut_value = false ) {
		return get_option( $key, $defalut_value );
	}

	/**
	 * Give a key, store the value
	 *
	 * @since  1.8.12s
	 *
	 * @param  string $key The option_name
	 * @param  mixed $value The value to store
	 *
	 * @return void
	 */
	public function update_option( $key, $value ) {
		update_option( $key, $value, false );
	}

	/**
	 * Delete an option
	 *
	 * @since  1.8.12
	 *
	 * @param  string $key The option_name to delete
	 *
	 * @return void
	 */
	public function delete_option( $key ) {
		delete_option( $key );
	}

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

	private function get_step_page() {
		return $this->get_option( $this->step_on_key, false );
	}
}
