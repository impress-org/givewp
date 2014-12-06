<?php
/**
 * Post Type Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and sets up the Downloads custom post type
 *
 * @since 1.0
 * @return void
 */
function give_setup_post_types() {

	$archives = defined( 'GIVE_DISABLE_ARCHIVE' ) && GIVE_DISABLE_ARCHIVE ? false : true;
	$slug     = defined( 'GIVE_SLUG' ) ? GIVE_SLUG : 'donations';
	$rewrite  = defined( 'GIVE_DISABLE_REWRITE' ) && GIVE_DISABLE_REWRITE ? false : array(
		'slug'       => $slug,
		'with_front' => false
	);

	$give_forms_labels = apply_filters( 'give_forms_labels', array(
		'name'               => '%2$s',
		'singular_name'      => '%1$s',
		'add_new'            => __( 'Add %1$s', 'give' ),
		'add_new_item'       => __( 'Add New %1$s', 'give' ),
		'edit_item'          => __( 'Edit %1$s', 'give' ),
		'new_item'           => __( 'New %1$s', 'give' ),
		'all_items'          => __( 'All %2$s', 'give' ),
		'view_item'          => __( 'View %1$s', 'give' ),
		'search_items'       => __( 'Search %2$s', 'give' ),
		'not_found'          => __( 'No %2$s found', 'give' ),
		'not_found_in_trash' => __( 'No %2$s found in Trash', 'give' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Give', 'give' )
	) );

	foreach ( $give_forms_labels as $key => $value ) {
		$give_forms_labels[ $key ] = sprintf( $value, give_get_label_singular(), give_get_label_plural() );
	}

	$give_forms_args = array(
		'labels'             => $give_forms_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => $rewrite,
		'map_meta_cap'       => true,
		'capability_type'    => 'give_forms',
		'has_archive'        => $archives,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'give_download_supports', array(
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'revisions',
			'author'
		) ),
	);
	register_post_type( 'give_forms', apply_filters( 'give_forms_post_type_args', $give_forms_args ) );


	/** Payment Post Type */
	$payment_labels = array(
		'name'               => _x( 'Payments', 'post type general name', 'give' ),
		'singular_name'      => _x( 'Payment', 'post type singular name', 'give' ),
		'add_new'            => __( 'Add New', 'give' ),
		'add_new_item'       => __( 'Add New Payment', 'give' ),
		'edit_item'          => __( 'Edit Payment', 'give' ),
		'new_item'           => __( 'New Payment', 'give' ),
		'all_items'          => __( 'All Payments', 'give' ),
		'view_item'          => __( 'View Payment', 'give' ),
		'search_items'       => __( 'Search Payments', 'give' ),
		'not_found'          => __( 'No Payments found', 'give' ),
		'not_found_in_trash' => __( 'No Payments found in Trash', 'give' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Payment History', 'give' )
	);

	$payment_args = array(
		'labels'          => apply_filters( 'give_payment_labels', $payment_labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'map_meta_cap'    => true,
		'capability_type' => 'give_payment',
		'supports'        => array( 'title' ),
		'can_export'      => true
	);
	register_post_type( 'give_payment', $payment_args );

}

add_action( 'init', 'give_setup_post_types', 1 );

/**
 * Get Default Labels
 *
 * @since 1.0.8.3
 * @return array $defaults Default labels
 */
function give_get_default_labels() {
	$defaults = array(
		'singular' => __( 'Form', 'give' ),
		'plural'   => __( 'Forms', 'give' )
	);

	return apply_filters( 'give_default_name', $defaults );
}

/**
 * Get Singular Label
 *
 * @since 1.0
 *
 * @param bool $lowercase
 *
 * @return string $defaults['singular'] Singular label
 */
function give_get_label_singular( $lowercase = false ) {
	$defaults = give_get_default_labels();

	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @since 1.0
 * @return string $defaults['plural'] Plural label
 */
function give_get_label_plural( $lowercase = false ) {
	$defaults = give_get_default_labels();

	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Change default "Enter title here" input
 *
 * @since 1.0
 *
 * @param string $title Default title placeholder text
 *
 * @return string $title New placeholder text
 */
function give_change_default_title( $title ) {
	// If a frontend plugin uses this filter (check extensions before changing this function)
	if ( ! is_admin() ) {
		$label = give_get_label_singular();
		$title = sprintf( __( 'Enter %s title here', 'give' ), $label );

		return $title;
	}

	$screen = get_current_screen();

	if ( 'give_forms' == $screen->post_type ) {
		$label = give_get_label_singular();
		$title = sprintf( __( 'Enter %s title here', 'give' ), $label );
	}

	return $title;
}

add_filter( 'enter_title_here', 'give_change_default_title' );

/**
 * Registers Custom Post Statuses which are used by the Payments and Discount
 * Codes
 *
 * @since 1.0
 * @return void
 */
function give_register_post_type_statuses() {
	// Payment Statuses
	register_post_status( 'refunded', array(
		'label'                     => _x( 'Refunded', 'Refunded payment status', 'give' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'give' )
	) );
	register_post_status( 'failed', array(
		'label'                     => _x( 'Failed', 'Failed payment status', 'give' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'give' )
	) );
	register_post_status( 'revoked', array(
		'label'                     => _x( 'Revoked', 'Revoked payment status', 'give' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Revoked <span class="count">(%s)</span>', 'Revoked <span class="count">(%s)</span>', 'give' )
	) );
	register_post_status( 'abandoned', array(
		'label'                     => _x( 'Abandoned', 'Abandoned payment status', 'give' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'give' )
	) );

	// Discount Code Statuses
	register_post_status( 'active', array(
		'label'                     => _x( 'Active', 'Active discount code status', 'give' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'give' )
	) );
	register_post_status( 'inactive', array(
		'label'                     => _x( 'Inactive', 'Inactive discount code status', 'give' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'give' )
	) );
}

add_action( 'init', 'give_register_post_type_statuses' );

/**
 * Updated Messages
 *
 * Returns an array of with all updated messages.
 *
 * @since 1.0
 *
 * @param array $messages Post updated message
 *
 * @return array $messages New post updated messages
 */
function give_updated_messages( $messages ) {
	global $post, $post_ID;

	$url1 = '<a href="' . get_permalink( $post_ID ) . '">';
	$url2 = give_get_label_singular();
	$url3 = '</a>';

	$messages['download'] = array(
		1 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'give' ), $url1, $url2, $url3 ),
		4 => sprintf( __( '%2$s updated. %1$sView %2$s%3$s.', 'give' ), $url1, $url2, $url3 ),
		6 => sprintf( __( '%2$s published. %1$sView %2$s%3$s.', 'give' ), $url1, $url2, $url3 ),
		7 => sprintf( __( '%2$s saved. %1$sView %2$s%3$s.', 'give' ), $url1, $url2, $url3 ),
		8 => sprintf( __( '%2$s submitted. %1$sView %2$s%3$s.', 'give' ), $url1, $url2, $url3 )
	);

	return $messages;
}

add_filter( 'post_updated_messages', 'give_updated_messages' );
