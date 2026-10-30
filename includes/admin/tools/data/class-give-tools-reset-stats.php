<?php
/**
 * Recount income and stats
 *
 * This class handles batch processing of resetting donations and income stats.
 *
 * @subpackage  Admin/Tools/Give_Tools_Reset_Stats
 * @copyright   Copyright (c) 2016, GiveWP
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
	 *
	 * @var string
	 * @since 1.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-form batch processing to be run.
	 *
	 * @since  1.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 *
	 * @since  1.5
	 * @var integer
	 */
	public $per_step = 30;

	/**
	 * Success message to display when reset is complete
	 *
	 * @since 4.10.0
	 * @var string
	 */
	public $message = '';

	/**
	 * Constructor.
	 */
	public function __construct( $_step = 1 ) {
		parent::__construct( $_step );

		$this->is_writable = true;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since TBD Delete the give_donations table rows as well, and stop when a query fails.
	 * @since TBD Document why the reset queries are safe, and clear the cache of the deleted options only.
	 * @since 4.10.0 Added deletion logic for campaigns, campaign pages, subscriptions, events, logs, revenue, usermeta, etc.
	 * @since  1.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *                      Database API
	 * @return bool   $data The data for the CSV file
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
			$deleted_options = [];

			$step_ids = [
				'customers' => [],
				'forms'     => [],
				'other'     => [],
			];

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

			$sql        = [];
			$meta_table = give_v20_bc_table_details( 'form' );
			$donor_meta_table_name = Give()->donor_meta->table_name;

			foreach ( $step_ids as $type => $ids ) {

				if ( empty( $ids ) ) {
					continue;
				}

				$ids = implode( ',', $ids );

				switch ( $type ) {
					case 'customers':
						// Delete all the Give related donor and its meta.
						$sql[] = "DELETE FROM {$wpdb->donors}";
						$sql[] = "DELETE FROM {$donor_meta_table_name}";
						break;
					case 'forms':
						$sql[] = "UPDATE {$meta_table['name']} SET meta_value = 0 WHERE meta_key = '_give_form_sales' AND {$meta_table['column']['id']} IN ($ids)";
						$sql[] = "UPDATE {$meta_table['name']} SET meta_value = 0.00 WHERE meta_key = '_give_form_earnings' AND {$meta_table['column']['id']} IN ($ids)";
						break;

					case 'other':
						/*
						 * Delete this batch's donations table rows, which no hook keeps in step with the post
						 * deletes below. They go first and are scoped to the batch: the next run finds its IDs
						 * from the surviving posts, so it can only finish a delete that stopped on the posts.
						 */
						if ( ! \Give\Donations\Storage\DonationStorageFlag::get()->isPosts() ) {
							$sql[] = "DELETE FROM {$wpdb->prefix}give_donations WHERE id IN ($ids)";
						}

						// Delete main entries of forms and donations exists in posts table.
						$sql[] = "DELETE FROM {$wpdb->posts} WHERE id IN ($ids)";

						// Delete all the meta rows of form exists in form meta table.
						$sql[] = "DELETE FROM {$wpdb->formmeta}";

						// Delete all the meta rows of donation exists in donation meta table.
						$sql[] = "DELETE FROM {$wpdb->prefix}give_donationmeta";

						// Delete all the Give related sequential ordering entries for donations.
						$sql[] = "DELETE FROM {$wpdb->prefix}give_sequential_ordering WHERE payment_id IN ($ids)";

						// Delete all the Give related comments and its meta.
						$sql[] = "DELETE FROM {$wpdb->give_comments}";
						$sql[] = "DELETE FROM {$wpdb->give_commentmeta}";

						// Delete all the Give sessions data.
						$sql[] = "DELETE FROM {$wpdb->prefix}give_sessions";

						// Delete all Give logs data.
						$sql[] = "DELETE FROM {$wpdb->prefix}give_log";

						// Delete all Give revenue data.
						$sql[] = "DELETE FROM {$wpdb->prefix}give_revenue";

						// Delete campaigns and related data
						$sql[] = "DELETE FROM {$wpdb->prefix}give_campaign_forms";
						$sql[] = "DELETE FROM {$wpdb->prefix}give_campaigns";

						// Delete GiveWP Campaign Pages and their meta data
						$sql[] = "DELETE FROM {$wpdb->posts} WHERE post_type = 'page' AND id IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'give_campaign_id')";
						$sql[] = "DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'give_campaign_id'";

						// Delete subscriptions and related data
						$sql[] = "DELETE FROM {$wpdb->prefix}give_subscriptionmeta";
						$sql[] = "DELETE FROM {$wpdb->prefix}give_subscriptions";

						// Delete events and related data
						$sql[] = "DELETE FROM {$wpdb->prefix}give_event_tickets";
						$sql[] = "DELETE FROM {$wpdb->prefix}give_event_ticket_types";
						$sql[] = "DELETE FROM {$wpdb->prefix}give_events";

						// Clear all Give user meta data (notices, preferences, etc.)
						$sql[] = "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%give%'";

						// Reset GiveWP options to default values (preserve essential ones)
						// Essential options that must be preserved to prevent plugin deactivation issues
						$essential_options = [
							'give_version',                    // Plugin version - needed for upgrade detection
							'give_completed_upgrades',         // Completed upgrade routines - prevents re-running
							'give_default_api_version',        // API version - needed for API functionality
							'_give_table_check',              // Database table check - prevents table recreation
							'give_temp_reset_ids',            // Temporary reset data used by this class
							'give_settings',                  // Plugin settings - preserve to avoid pages recreation
							'give_donation_storage',          // Donation storage flag - its migration never runs again
						];

						// Create SQL placeholder string for the essential options list
						$essential_options_placeholder = implode( "','", $essential_options );
						// Delete all GiveWP options except the essential ones to prevent deactivation issues
						$options_where   = "option_name LIKE '%give%' AND option_name NOT IN ('{$essential_options_placeholder}')";
						$deleted_options = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE {$options_where}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- reads the names the next query deletes, so their cache entries can be cleared; the essential option names are fixed strings above.
						$sql[]           = "DELETE FROM {$wpdb->options} WHERE {$options_where}";

						// Clear Action Scheduler data related to GiveWP
						$sql[] = "DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook LIKE '%give%'";
						$sql[] = "DELETE FROM {$wpdb->prefix}actionscheduler_groups WHERE slug LIKE '%give%'";

						// Delete Give related categories and tags data from taxonomy tables.
						$sql[] = $wpdb->prepare(
							"
							DELETE FROM $wpdb->terms
							WHERE $wpdb->terms.term_id IN
							(
								SELECT $wpdb->term_taxonomy.term_id
								FROM $wpdb->term_taxonomy
								WHERE $wpdb->term_taxonomy.taxonomy = %s
								OR $wpdb->term_taxonomy.taxonomy = %s
							)
							",
							[ 'give_forms_category', 'give_forms_tag' ]
						);

						$sql[] = $wpdb->prepare(
							"
							DELETE FROM $wpdb->term_taxonomy
							WHERE $wpdb->term_taxonomy.taxonomy = %s
							OR $wpdb->term_taxonomy.taxonomy = %s
							",
							[ 'give_forms_category', 'give_forms_tag' ]
						);

						break;
				}

				if ( ! in_array( $type, [ 'customers', 'forms', 'other' ] ) ) {
					// Allows other types of custom post types to filter on their own post_type
					// and add items to the query list, for the IDs found in their post type.
					$sql = apply_filters( "give_reset_add_queries_{$type}", $sql, $ids );
				}
			}

			if ( is_array( $sql ) && count( $sql ) > 0 ) {
				foreach ( $sql as $query ) {
					if ( false === $wpdb->query( $query ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- one-off bulk reset of GiveWP tables, posts and options; no WordPress API or object cache covers it. Table and column names come from give_v20_bc_table_details() and Give()->donor_meta; ids are (int) post or donor ids or values added by the give_reset_items filter; the give_reset_add_queries_{$type} filter lets add-ons append raw SQL that runs as-is.
						// Stop here rather than acknowledge the step: the two donation storages
						// would otherwise be left out of step with no way to tell.
						wp_die(
							esc_html__( 'A database query failed while resetting data. Check the database error log, then run the tool again.', 'give' ),
							esc_html__( 'Error', 'give' ),
							[
								'response' => 500,
							]
						);
					}
				}

				// The raw delete above skips the options cache. Clear it so the batch tools do not read removed options.
				$this->clear_options_cache( $deleted_options );
			}

			return true;

		}// End if().

		return false;

	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 4.10.0 Check if items are set before counting to prevent fatal errors on PHP 8
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$items = $this->get_stored_data( 'give_temp_reset_ids' );
		$total = $items ? count( $items ) : 0;

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
	 * Set the properties specific to the payments export.
	 *
	 * @since 1.5
	 *
	 * @param array $request The Form Data passed into the batch processing.
	 */
	public function set_properties( $request ) {
	}

	/**
	 * Process a step
	 *
	 * @since 4.10.0 Updated success message
	 * @since 1.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die(
				esc_html__( 'You do not have permission to reset data.', 'give' ),
				esc_html__( 'Error', 'give' ),
				[
					'response' => 403,
				]
			);
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			update_option( 'give_earnings_total', 0, false );
			Give_Cache::delete( Give_Cache::get_key( 'give_estimated_monthly_stats' ) );

			$this->delete_data( 'give_temp_reset_ids' );

			$this->done = true;
			$this->message = esc_html__( 'Successfully reset data for campaigns, campaign pages, donation forms, subscriptions, events, revenue, donation counts, logs, etc.', 'give' );

			return false;
		}
	}

	/**
	 * Headers
	 */
	public function headers() {
		give_ignore_user_abort();
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
			$items = [];

			$give_types_for_reset = [ 'give_forms', 'give_payment' ];
			$give_types_for_reset = apply_filters( 'give_reset_store_post_types', $give_types_for_reset );

			$args = apply_filters(
				'give_tools_reset_stats_total_args',
				[
					'post_type'      => $give_types_for_reset,
					'post_status'    => 'any',
					'posts_per_page' => - 1,
				]
			);

			$posts = get_posts( $args );
			foreach ( $posts as $post ) {
				$items[] = [
					'id'   => (int) $post->ID,
					'type' => $post->post_type,
				];
			}

			$donor_args = [
				'number' => - 1,
			];
			$donors     = Give()->donors->get_donors( $donor_args );
			foreach ( $donors as $donor ) {
				$items[] = [
					'id'   => (int) $donor->id,
					'type' => 'customer',
				];
			}


			// Allow filtering of items to remove with an unassociative array for each item
			// The array contains the unique ID of the item, and a 'type' for you to use in the execution of the get_data method
			$items = apply_filters( 'give_reset_items', $items );

			$this->store_data( 'give_temp_reset_ids', $items );
		}// End if().

	}

	/**
	 * Given a key, get the information from the Database Directly.
	 *
	 * @since TBD Use the options API.
	 * @since  1.5
	 *
	 * @param  string $key The option_name
	 *
	 * @return mixed       Returns the data from the database.
	 */
	private function get_stored_data( $key ) {
		$value = get_option( $key, null );

		if ( empty( $value ) ) {
			return false;
		}

		$maybe_json = json_decode( $value );
		if ( ! is_null( $maybe_json ) ) {
			$value = json_decode( $value, true );
		}

		return (array) $value;
	}

	/**
	 * Give a key, store the value.
	 *
	 * @since TBD Use the options API.
	 * @since  1.5
	 *
	 * @param  string $key   The option_name.
	 * @param  mixed  $value The value to store.
	 *
	 * @return void
	 */
	private function store_data( $key, $value ) {
		$value = is_array( $value ) ? wp_json_encode( $value ) : esc_attr( $value );

		update_option( $key, $value, false );
	}

	/**
	 * Remove the given options from the object cache, without flushing the rest of the cache.
	 *
	 * @since TBD
	 *
	 * @param string[] $option_names Names of options deleted with a raw query.
	 *
	 * @return void
	 */
	private function clear_options_cache( $option_names ) {
		if ( empty( $option_names ) ) {
			return;
		}

		foreach ( $option_names as $option_name ) {
			wp_cache_delete( $option_name, 'options' );
		}

		wp_cache_delete( 'alloptions', 'options' );
		wp_cache_delete( 'notoptions', 'options' );
	}

	/**
	 * Delete an option
	 *
	 * @since TBD Use the options API.
	 * @since  1.5
	 *
	 * @param  string $key The option_name to delete
	 *
	 * @return void
	 */
	private function delete_data( $key ) {
		delete_option( $key );
	}

	/**
	 * Unset the properties specific to the donors export.
	 *
	 * @since 2.3.0
	 *
	 * @param array             $request
	 * @param Give_Batch_Export $export
	 */
	public function unset_properties( $request, $export ) {
		if ( $export->done ) {
			// Delete all the donation ids.
			$this->delete_data( 'give_temp_reset_ids' );
		}
	}

}
