<?php
/**
 * Recount income and stats
 *
 * This class handles batch processing of resetting donations and income stats
 *
 * @subpackage  Admin/Tools/Give_Tools_Reset_Stats
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Tools_Reset_Stats Class
 *
 * @since 1.5
 */
class Give_Tools_Reset_Stats extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-form batch processing to be run.
	 * @since  1.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  1.5
	 * @var integer
	 */
	public $per_step = 30;

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  1.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *                      Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$items = $this->get_stored_data( 'give_temp_reset_ids' );

		if ( ! is_array( $items ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $items, $offset, $this->per_step );

		if ( $step_items ) {

			$step_ids = array(
				'customers' => array(),
				'forms'     => array(),
				'other'     => array(),
			);

			foreach ( $step_items as $item ) {

				switch ( $item['type'] ) {
					case 'customer':
						$step_ids['customers'][] = $item['id'];
						break;
					case 'forms':
						$step_ids['give_forms'][] = $item['id'];
						break;
					default:
						$item_type                = apply_filters( 'give_reset_item_type', 'other', $item );
						$step_ids[ $item_type ][] = $item['id'];
						break;
				}

			}

			$sql = array();

			foreach ( $step_ids as $type => $ids ) {

				if ( empty( $ids ) ) {
					continue;
				}

				$ids = implode( ',', $ids );

				switch ( $type ) {
					case 'customers':
						$table_name = $wpdb->prefix . 'give_customers';
						$sql[]      = "DELETE FROM $table_name WHERE id IN ($ids)";
						break;
					case 'forms':
						$sql[] = "UPDATE $wpdb->postmeta SET meta_value = 0 WHERE meta_key = '_give_form_sales' AND post_id IN ($ids)";
						$sql[] = "UPDATE $wpdb->postmeta SET meta_value = 0.00 WHERE meta_key = '_give_form_earnings' AND post_id IN ($ids)";
						break;
					case 'other':
						$sql[] = "DELETE FROM $wpdb->posts WHERE id IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->postmeta WHERE post_id IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->comments WHERE comment_post_ID IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)";
						break;
				}

				if ( ! in_array( $type, array( 'customers', 'forms', 'other' ) ) ) {
					// Allows other types of custom post types to filter on their own post_type
					// and add items to the query list, for the IDs found in their post type.
					$sql = apply_filters( "give_reset_add_queries_{$type}", $sql, $ids );
				}


			}

			if ( ! empty( $sql ) ) {
				foreach ( $sql as $query ) {
					$wpdb->query( $query );
				}
			}

			return true;

		}

		return false;

	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$items = $this->get_stored_data( 'give_temp_reset_ids', false );
		$total = count( $items );

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 1.5
	 *
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
	}

	/**
	 * Process a step
	 *
	 * @since 1.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( esc_html__( 'You do not have permission to reset data.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			update_option( 'give_earnings_total', 0 );
			delete_transient( 'give_earnings_total' );
			delete_transient( 'give_estimated_monthly_stats' . true );
			delete_transient( 'give_estimated_monthly_stats' . false );
			$this->delete_data( 'give_temp_reset_ids' );

			// Reset the sequential order numbers
			if ( give_get_option( 'enable_sequential' ) ) {
				delete_option( 'give_last_payment_number' );
			}

			$this->done    = true;
			$this->message = esc_html__( 'Donation forms, income, donations counts, and logs successfully reset.', 'give' );

			return false;
		}
	}

	/**
	 * Headers
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}
	}

	/**
	 * Perform the export
	 *
	 * @access public
	 * @since  1.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		give_die();
	}

	/**
	 * Pre Fetch
	 */
	public function pre_fetch() {

		if ( $this->step == 1 ) {
			$this->delete_data( 'give_temp_reset_ids' );
		}

		$items = get_option( 'give_temp_reset_ids', false );

		if ( false === $items ) {
			$items = array();

			$give_types_for_reset = array( 'give_forms', 'give_log', 'give_payment' );
			$give_types_for_reset = apply_filters( 'give_reset_store_post_types', $give_types_for_reset );

			$args = apply_filters( 'give_tools_reset_stats_total_args', array(
				'post_type'      => $give_types_for_reset,
				'post_status'    => 'any',
				'posts_per_page' => - 1,
			) );

			$posts = get_posts( $args );
			foreach ( $posts as $post ) {
				$items[] = array(
					'id'   => (int) $post->ID,
					'type' => $post->post_type,
				);
			}

			$customer_args = array( 'number' => - 1 );
			$customers     = Give()->customers->get_customers( $customer_args );
			foreach ( $customers as $customer ) {
				$items[] = array(
					'id'   => (int) $customer->id,
					'type' => 'customer',
				);
			}

			// Allow filtering of items to remove with an unassociative array for each item
			// The array contains the unique ID of the item, and a 'type' for you to use in the execution of the get_data method
			$items = apply_filters( 'give_reset_items', $items );

			$this->store_data( 'give_temp_reset_ids', $items );
		}

	}

	/**
	 * Given a key, get the information from the Database Directly.
	 *
	 * @since  1.5
	 *
	 * @param  string $key The option_name
	 *
	 * @return mixed       Returns the data from the database.
	 */
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		return empty( $value ) ? false : maybe_unserialize( $value );
	}

	/**
	 * Give a key, store the value.
	 *
	 * @since  1.5
	 *
	 * @param  string $key   The option_name.
	 * @param  mixed  $value The value to store.
	 *
	 * @return void
	 */
	private function store_data( $key, $value ) {
		global $wpdb;

		$value = maybe_serialize( $value );

		$data = array(
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		);

		$formats = array(
			'%s',
			'%s',
			'%s',
		);

		$wpdb->replace( $wpdb->options, $data, $formats );
	}

	/**
	 * Delete an option
	 *
	 * @since  1.5
	 *
	 * @param  string $key The option_name to delete
	 *
	 * @return void
	 */
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
