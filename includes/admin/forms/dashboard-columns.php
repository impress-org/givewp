<?php
/**
 * Dashboard Columns
 *
 * @package     GIVE
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, WordImpress
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
	$give_form_columns = array(
		'cb'            => '<input type="checkbox"/>',
		'title'         => __( 'Name', 'give' ),
		'form_category' => __( 'Categories', 'give' ),
		'form_tag'      => __( 'Tags', 'give' ),
		'price'         => __( 'Amount', 'give' ),
		'goal'          => __( 'Goal', 'give' ),
		'donations'     => __( 'Donations', 'give' ),
		'earnings'      => __( 'Income', 'give' ),
		'shortcode'     => __( 'Shortcode', 'give' ),
		'date'          => __( 'Date', 'give' ),
	);

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
						give_currency_filter( give_format_amount( give_get_form_earnings_stats( $post_id ), array( 'sanitize' => false ) ) )
					);
				} else {
					echo '-';
				}
				break;
			case 'shortcode':
				printf( '<input onclick="this.setSelectionRange(0, this.value.length)" type="text" class="shortcode-input" readonly="" value="[give_form id=&#34;%s&#34;]"', absint( $post_id ) );
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
				array(
					'meta_key' => '_give_form_sales',
					'orderby'  => 'meta_value_num',
				)
			);
			break;

		// Check if "orderby" is set to "earnings".
		case 'earnings':
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_give_form_earnings',
					'orderby'  => 'meta_value_num',
				)
			);
			break;

		// Check if "orderby" is set to "price/amount".
		case 'amount':
			$multi_level_meta_key = ( 'asc' === $vars['order'] ) ? '_give_levels_minimum_amount' : '_give_levels_maximum_amount';

			$vars['orderby']    = 'meta_value_num';
			$vars['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'  => $multi_level_meta_key,
					'type' => 'NUMERIC',
				),
				array(
					'key'  => '_give_set_price',
					'type' => 'NUMERIC',
				)
			);

			break;

		// Check if "orderby" is set to "goal".
		case 'goal':
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_give_set_goal',
					'orderby'  => 'meta_value_num',
				)
			);
			break;

		// Check if "orderby" is set to "donations".
		case 'donations':
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_give_form_sales',
					'orderby'  => 'meta_value_num',
				)
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
				wp_die( esc_html__( 'You do not have permission to view this data.', 'give' ), esc_html__( 'Error', 'give' ), array(
					'response' => 403,
				) );
			}
			$vars = array_merge(
				$vars,
				array(
					'author' => get_current_user_id(),
				)
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
		$dates = array();
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
 * Process bulk edit actions via AJAX
 *
 * @since 1.0
 * @return void
 */
function give_save_bulk_edit() {

	$post_ids = ( isset( $_POST['post_ids'] ) && ! empty( $_POST['post_ids'] ) ) ? $_POST['post_ids'] : array();

	if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
		$price = isset( $_POST['price'] ) ? strip_tags( stripslashes( $_POST['price'] ) ) : 0;
		foreach ( $post_ids as $post_id ) {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				continue;
			}

			if ( ! empty( $price ) ) {
				give_update_meta( $post_id, '_give_set_price', give_sanitize_amount_for_db( $price ) );
			}
		}
	}

	die();
}

add_action( 'wp_ajax_give_save_bulk_edit', 'give_save_bulk_edit' );
