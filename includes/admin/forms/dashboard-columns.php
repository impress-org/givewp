<?php
/**
 * Dashboard Columns
 *
 * @package     GIVE
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Give Forms Columns
 *
 * Defines the custom columns and their order
 *
 * @since 1.0
 *
 * @param array $give_form_columns Array of forms columns
 *
 * @return array $form_columns Updated array of forms columns
 *  Post Type List Table
 */
function give_form_columns( $give_form_columns ) {

	// Standard columns
	$give_form_columns = [
		'cb'            => '<input type="checkbox"/>',
		'title'         => __( 'Name', 'give' ),
		'form_category' => __( 'Categories', 'give' ),
		'form_tag'      => __( 'Tags', 'give' ),
		'price'         => __( 'Amount', 'give' ),
		'goal'          => __( 'Goal', 'give' ),
		'donations'     => __( 'Donations', 'give' ),
		'earnings'      => __( 'Revenue', 'give' ),
		'shortcode'     => __( 'Shortcode', 'give' ),
		'date'          => __( 'Date', 'give' ),
	];

	// Does the user want categories / tags?
	if ( ! give_is_setting_enabled( give_get_option( 'categories', 'disabled' ) ) ) {
		unset( $give_form_columns['form_category'] );
	}
	if ( ! give_is_setting_enabled( give_get_option( 'tags', 'disabled' ) ) ) {
		unset( $give_form_columns['form_tag'] );
	}

	return apply_filters( 'give_forms_columns', $give_form_columns );
}

add_filter( 'manage_edit-give_forms_columns', 'give_form_columns' );

/**
 * Render Give Form Columns
 *
 * @since 1.0
 *
 * @param string $column_name Column name
 * @param int    $post_id     Give Form (Post) ID
 *
 * @return void
 */
function give_render_form_columns( $column_name, $post_id ) {
	if ( get_post_type( $post_id ) == 'give_forms' ) {

		switch ( $column_name ) {
			case 'form_category':
				echo get_the_term_list( $post_id, 'give_forms_category', '', ', ', '' );
				break;
			case 'form_tag':
				echo get_the_term_list( $post_id, 'give_forms_tag', '', ', ', '' );
				break;
			case 'price':
				if ( give_has_variable_prices( $post_id ) ) {
					echo give_price_range( $post_id );
				} else {
					echo give_price( $post_id, false );
					printf( '<input type="hidden" class="formprice-%1$s" value="%2$s" />', esc_attr( $post_id ), esc_attr( give_get_form_price( $post_id ) ) );
				}
				break;
			case 'goal':
				if ( give_is_setting_enabled( give_get_meta( $post_id, '_give_goal_option', true ) ) ) {

					echo give_admin_form_goal_stats( $post_id );

				} else {
					_e( 'No Goal Set', 'give' );
				}

				printf(
					'<input type="hidden" class="formgoal-%1$s" value="%2$s" />',
					esc_attr( $post_id ),
					give_get_form_goal( $post_id )
				);

				break;
			case 'donations':
				if ( current_user_can( 'view_give_form_stats', $post_id ) ) {
					printf(
						'<a href="%1$s">%2$s</a>',
						esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&form_id=' . $post_id ) ),
						give_get_form_sales_stats( $post_id )
					);
				} else {
					echo '-';
				}
				break;
			case 'earnings':
				if ( current_user_can( 'view_give_form_stats', $post_id ) ) {
					printf(
						'<a href="%1$s">%2$s</a>',
						esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-reports&tab=forms&form-id=' . $post_id ) ),
						give_currency_filter( give_format_amount( give_get_form_earnings_stats( $post_id ), [ 'sanitize' => false ] ) )
					);
				} else {
					echo '-';
				}
				break;
			case 'shortcode':
				$shortcode = sprintf( '[give_form id="%s"]', absint( $post_id ) );
				printf(
					'<button
							type="button"
							class="button hint-tooltip hint--top js-give-shortcode-button"
							aria-label="%1$s"
							data-give-shortcode="%2$s">
						<span class="dashicons dashicons-admin-page"></span>
						<span class="give-button-text"> %3$s</span>
					</button>',
					esc_attr( $shortcode ),
					esc_attr( $shortcode ),
					esc_html__( 'Copy Shortcode', 'give' )
				);
				break;
		}// End switch().
	}// End if().
}

add_action( 'manage_posts_custom_column', 'give_render_form_columns', 10, 2 );

/**
 * Registers the sortable columns in the list table
 *
 * @since 1.0
 *
 * @param array $columns Array of the columns
 *
 * @return array $columns Array of sortable columns
 */
function give_sortable_form_columns( $columns ) {
	$columns['price']     = 'amount';
	$columns['sales']     = 'sales';
	$columns['earnings']  = 'earnings';
	$columns['goal']      = 'goal';
	$columns['donations'] = 'donations';

	return $columns;
}

add_filter( 'manage_edit-give_forms_sortable_columns', 'give_sortable_form_columns' );

/**
 * Sorts Columns in the Forms List Table
 *
 * @since 1.0
 *
 * @param array $vars Array of all the sort variables.
 *
 * @return array $vars Array of all the sort variables.
 */
function give_sort_forms( $vars ) {
	// Check if we're viewing the "give_forms" post type.
	if ( ! isset( $vars['post_type'] ) || ! isset( $vars['orderby'] ) || 'give_forms' !== $vars['post_type'] ) {
		return $vars;
	}

	switch ( $vars['orderby'] ) {
		// Check if 'orderby' is set to "sales".
		case 'sales':
			$vars = array_merge(
				$vars,
				[
					'meta_key' => '_give_form_sales',
					'orderby'  => 'meta_value_num',
				]
			);
			break;

		// Check if "orderby" is set to "earnings".
		case 'earnings':
			$vars = array_merge(
				$vars,
				[
					'meta_key' => '_give_form_earnings',
					'orderby'  => 'meta_value_num',
				]
			);
			break;

		// Check if "orderby" is set to "price/amount".
		case 'amount':
			$multi_level_meta_key = ( 'asc' === $vars['order'] ) ? '_give_levels_minimum_amount' : '_give_levels_maximum_amount';

			$vars['orderby']    = 'meta_value_num';
			$vars['meta_query'] = [
				'relation' => 'OR',
				[
					'key'  => $multi_level_meta_key,
					'type' => 'NUMERIC',
				],
				[
					'key'  => '_give_set_price',
					'type' => 'NUMERIC',
				],
			];

			break;

		// Check if "orderby" is set to "goal".
		case 'goal':
			$meta_key = give_has_upgrade_completed( 'v240_update_form_goal_progress' )
				? '_give_form_goal_progress'
				: '_give_set_goal'; // Backward compatibility

			$vars = array_merge(
				$vars,
				[
					'meta_key' => $meta_key,
					'orderby'  => 'meta_value_num',
				]
			);
			break;

		// Check if "orderby" is set to "donations".
		case 'donations':
			$vars = array_merge(
				$vars,
				[
					'meta_key' => '_give_form_sales',
					'orderby'  => 'meta_value_num',
				]
			);
			break;
	}// End switch().

	return $vars;
}

/**
 * Sets restrictions on author of Forms List Table
 *
 * @since  1.0
 *
 * @param  array $vars Array of all sort variables.
 *
 * @return array       Array of all sort variables.
 */
function give_filter_forms( $vars ) {
	if ( isset( $vars['post_type'] ) && 'give_forms' == $vars['post_type'] ) {

		// If an author ID was passed, use it
		if ( isset( $_REQUEST['author'] ) && ! current_user_can( 'view_give_reports' ) ) {

			$author_id = $_REQUEST['author'];
			if ( (int) $author_id !== get_current_user_id() ) {
				wp_die(
					esc_html__( 'You do not have permission to view this data.', 'give' ),
					esc_html__( 'Error', 'give' ),
					[
						'response' => 403,
					]
				);
			}
			$vars = array_merge(
				$vars,
				[
					'author' => get_current_user_id(),
				]
			);

		}
	}

	return $vars;
}

/**
 * Form Load
 *
 * Sorts the form columns.
 *
 * @since 1.0
 * @return void
 */
function give_forms_load() {
	add_filter( 'request', 'give_sort_forms' );
	add_filter( 'request', 'give_filter_forms' );
}

add_action( 'load-edit.php', 'give_forms_load', 9999 );

/**
 * Remove Forms Month Filter
 *
 * Removes the default drop down filter for forms by date.
 *
 * @since  1.0
 *
 * @param array $dates   The preset array of dates.
 *
 * @global      $typenow The post type we are viewing.
 * @return array Empty array disables the dropdown.
 */
function give_remove_month_filter( $dates ) {
	global $typenow;

	if ( $typenow == 'give_forms' ) {
		$dates = [];
	}

	return $dates;
}

add_filter( 'months_dropdown_results', 'give_remove_month_filter', 99 );

/**
 * Updates price when saving post
 *
 * @since 1.0
 * @since 2.1.4 If the donation amount is less than the Minimum amount then set the donation amount as Donation minimum amount.
 *
 * @param int $post_id Download (Post) ID
 *
 * @return int|null
 */
function give_price_save_quick_edit( $post_id ) {
	if ( ! isset( $_POST['post_type'] ) || 'give_forms' !== $_POST['post_type'] ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	if ( isset( $_REQUEST['_give_regprice'] ) ) {
		give_update_meta( $post_id, '_give_set_price', give_sanitize_amount_for_db( strip_tags( stripslashes( $_REQUEST['_give_regprice'] ) ) ) );
	}

	// Override the Donation minimum amount.
	if (
		isset( $_REQUEST['_give_custom_amount'], $_REQUEST['_give_set_price'], $_REQUEST['_give_price_option'], $_REQUEST['_give_custom_amount_range'] )
		&& 'set' === $_REQUEST['_give_price_option']
		&& give_is_setting_enabled( $_REQUEST['_give_custom_amount'] )
		&& give_maybe_sanitize_amount( $_REQUEST['_give_set_price'] ) < give_maybe_sanitize_amount( $_REQUEST['_give_custom_amount_range']['minimum'] )
	) {
		give_update_meta( $post_id, '_give_custom_amount_range_minimum', give_sanitize_amount_for_db( $_REQUEST['_give_set_price'] ) );
	}
}

add_action( 'save_post', 'give_price_save_quick_edit' );

/**
 * Function is used to filter the query for search result.
 *
 * @since 2.4.0
 *
 * @param $wp WP WordPress environment instance (passed by reference).
 */
function give_form_search_query_filter( $wp ) {

	if (
		isset( $wp->query_vars['post_type'] )
		 && 'give_forms' == $wp->query_vars['post_type']
		&& isset( $_GET['give-forms-goal-filter'] )
	) {

		$wp->query_vars['date_query'] =
			[
				'after'     => ! empty( $_GET['start-date'] ) ? date( 'Y-m-d', strtotime( give_clean( $_GET['start-date'] ) ) ) : false,
				'before'    => ! empty( $_GET['end-date'] ) ? date( 'Y-m-d 23:59:59 ', strtotime( give_clean( $_GET['end-date'] ) ) ) : false,
				'inclusive' => true,
			];
		switch ( $_GET['give-forms-goal-filter'] ) {
			case 'goal_in_progress':
				$wp->query_vars['meta_query'] =
					[
						'relation' => 'AND',
						[
							'key'     => '_give_form_goal_progress',
							'value'   => [ 1, 99 ],
							'compare' => 'BETWEEN',
							'type'    => 'NUMERIC',
						],
					];

				break;
			case 'goal_achieved':
				$wp->query_vars['meta_query'] =
					[
						'relation' => 'AND',
						[
							'key'     => '_give_form_goal_progress',
							'value'   => 100,
							'compare' => '>=',
							'type'    => 'NUMERIC',
						],
					];
				break;
			case 'goal_not_set':
				$wp->query_vars['meta_query'] =
					[
						'relation' => 'OR',
						[
							'key'     => '_give_goal_option',
							'value'   => 'disabled',
							'compare' => '=',
						],
						[
							'key'     => '_give_goal_option',
							'compare' => 'NOT EXISTS',
						],
					];
				break;
		}
	}
}

add_action( 'parse_request', 'give_form_search_query_filter' );

/**
 * function is used to search give forms by ID or title.
 *
 * @since 2.4.0
 *
 * @param $query WP_Query the WP_Query instance (passed by reference).
 */

function give_search_form_by_id( $query ) {
	// Verify that we are on the give forms list page.
	if (
		empty( $query->query_vars['post_type'] )
		|| 'give_forms' !== $query->query_vars['post_type']
	) {
		return;
	}

	if ( '' !== $query->query_vars['s'] && is_search() ) {
		if ( absint( $query->query_vars['s'] ) ) {
			// Set the post id value
			$query->set( 'p', $query->query_vars['s'] );
			// Reset the search value
			$query->set( 's', '' );
		}
	}
}

add_filter( 'pre_get_posts', 'give_search_form_by_id' );

/**
 * Outputs advanced filter html in Give forms list admin screen.
 *
 * @sicne 2.4.0
 *
 * @param $which
 */
function give_forms_advanced_filter( $which ) {
	/* @var stdClass $screen */
	$screen = get_current_screen();

	if ( 'edit' !== $screen->parent_base || 'give_forms' !== $screen->post_type ) {
		return;
	}

	// Apply this only on a specific post type
	if ( 'top' !== $which ) {
		return;
	}

	$start_date             = isset( $_GET['start-date'] ) ? strtotime( give_clean( $_GET['start-date'] ) ) : '';
	$end_date               = isset( $_GET['end-date'] ) ? strtotime( give_clean( $_GET['end-date'] ) ) : '';
	$search                 = isset( $_GET['s'] ) ? give_clean( $_GET['s'] ) : '';
	$give_forms_goal_filter = isset( $_GET['give-forms-goal-filter'] ) ? $_GET['give-forms-goal-filter'] : '';
	?>
	<div id="give-forms-advanced-filter" class="give-filters">
		<div class="give-filter give-filter-search">
			<input type="text" id="give-forms-search-input" placeholder="<?php _e( 'Form Name or ID', 'give' ); ?>" name="s" value="<?php echo esc_attr( $search ); ?>">
			<?php
			submit_button(
				__( 'Search', 'give' ),
				'button',
				false,
				false,
				[
					'ID' => 'form-search-submit',
				]
			);
			?>
		</div>
		<div id="give-payment-date-filters">
			<div class="give-filter give-filter-half">
				<label for="start-date"
					   class="give-start-date-label"><?php _e( 'Start Date', 'give' ); ?></label>
				<input type="text"
					   id="start-date"
					   name="start-date"
					   class="give_datepicker"
					   autocomplete="off"
					   value="<?php echo $start_date ? date_i18n( give_date_format(), $start_date ) : ''; ?>"
					   data-standard-date="<?php echo $start_date ? date( 'Y-m-d', $start_date ) : $start_date; ?>"
					   placeholder="<?php _e( 'Start Date', 'give' ); ?>"
				/>
			</div>
			<div class="give-filter give-filter-half">
				<label for="end-date" class="give-end-date-label"><?php _e( 'End Date', 'give' ); ?></label>
				<input type="text"
					   id="end-date"
					   name="end-date"
					   class="give_datepicker"
					   autocomplete="off"
					   value="<?php echo $end_date ? date_i18n( give_date_format(), $end_date ) : ''; ?>"
					   data-standard-date="<?php echo $end_date ? date( 'Y-m-d', $end_date ) : $end_date; ?>"
					   placeholder="<?php _e( 'End Date', 'give' ); ?>"
				/>
			</div>
		</div>
		<div id="give-payment-form-filter" class="give-filter">
			<label for="give-donation-forms-filter"
				   class="give-donation-forms-filter-label"><?php _e( 'Goal', 'give' ); ?></label>
			<select id="give-forms-goal-filter" name="give-forms-goal-filter" class="give-forms-goal-filter">
				<option value="any_goal_status"
				<?php
				if ( 'any_goal_status' === $give_forms_goal_filter ) {
					echo 'selected';
				}
				?>
				><?php _e( 'Any Goal Status', 'give' ); ?></option>
				<option value="goal_achieved"
				<?php
				if ( 'goal_achieved' === $give_forms_goal_filter ) {
					echo 'selected';
				}
				?>
				><?php _e( 'Goal Achieved', 'give' ); ?></option>
				<option value="goal_in_progress"
				<?php
				if ( 'goal_in_progress' === $give_forms_goal_filter ) {
					echo 'selected';
				}
				?>
				><?php _e( 'Goal In Progress', 'give' ); ?></option>
				<option value="goal_not_set"
				<?php
				if ( 'goal_not_set' === $give_forms_goal_filter ) {
					echo 'selected';
				}
				?>
				><?php _e( 'Goal Not Set', 'give' ); ?></option>
			</select>
		</div>
		<div class="give-filter">
			<?php submit_button( __( 'Apply', 'give' ), 'secondary', '', false ); ?>
			<?php
			// Clear active filters button.
			if ( ! empty( $start_date ) || ! empty( $end_date ) || ! empty( $search ) || ! empty( $give_forms_goal_filter ) ) :
				?>
				<a href="<?php echo admin_url( 'edit.php?post_type=give_forms' ); ?>"
				   class="button give-clear-filters-button"><?php _e( 'Clear Filters', 'give' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

add_action( 'manage_posts_extra_tablenav', 'give_forms_advanced_filter', 10, 1 );
