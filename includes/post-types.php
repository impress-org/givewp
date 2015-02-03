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

	/** Give Forms Post Type */
	$give_forms_archives = defined( 'GIVE_DISABLE_FORMS_ARCHIVE' ) && GIVE_DISABLE_FORMS_ARCHIVE ? false : true;
	$give_forms_slug     = defined( 'GIVE_FORMS_SLUG' ) ? GIVE_FORMS_SLUG : 'donations';
	$give_forms_rewrite  = defined( 'GIVE_DISABLE_FORMS_REWRITE' ) && GIVE_DISABLE_FORMS_REWRITE ? false : array(
		'slug'       => $give_forms_slug,
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
		$give_forms_labels[ $key ] = sprintf( $value, give_get_forms_label_singular(), give_get_forms_label_plural() );
	}

	$give_forms_args = array(
		'labels'             => $give_forms_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => $give_forms_rewrite,
		'map_meta_cap'       => true,
		'capability_type'    => 'give_forms',
		'has_archive'        => $give_forms_archives,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'give_forms_supports', array(
			'title',
			'thumbnail',
			'excerpt',
			'revisions',
			'author'
		) ),
	);
	register_post_type( 'give_forms', apply_filters( 'give_forms_post_type_args', $give_forms_args ) );


	/** Give Campaigns Post Type */
	$give_campaigns_archives = defined( 'GIVE_DISABLE_CAMPAIGNS_ARCHIVE' ) && GIVE_DISABLE_CAMPAIGNS_ARCHIVE ? false : true;
	$give_campaigns_slug     = defined( 'GIVE_CAMPAIGNS_SLUG' ) ? GIVE_CAMPAIGNS_SLUG : 'campaigns';
	$give_campaigns_rewrite  = defined( 'GIVE_DISABLE_CAMPAIGNS_REWRITE' ) && GIVE_DISABLE_CAMPAIGNS_REWRITE ? false : array(
		'slug'       => $give_campaigns_slug,
		'with_front' => false
	);

	$give_campaigns_labels = apply_filters( 'give_campaign_labels', array(
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
	) );

	foreach ( $give_campaigns_labels as $key => $value ) {
		$give_campaigns_labels[ $key ] = sprintf( $value, give_get_campaigns_label_singular(), give_get_campaigns_label_plural() );
	}

	$give_campaigns_args = array(
		'labels'             => $give_campaigns_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => 'edit.php?post_type=give_forms',
		'query_var'          => true,
		'rewrite'            => $give_campaigns_rewrite,
		'map_meta_cap'       => true,
		'capability_type'    => 'give_campaigns',
		'has_archive'        => $give_campaigns_archives,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'give_campaigns_supports', array(
			'title',
			'thumbnail',
			'excerpt',
			'revisions',
			'author'
		) ),
	);
	register_post_type( 'give_campaigns', apply_filters( 'give_campaigns_post_type_args', $give_campaigns_args ) );

	/** Payment Post Type */
	$payment_labels = array(
		'name'               => _x( 'Donations', 'post type general name', 'give' ),
		'singular_name'      => _x( 'Donation', 'post type singular name', 'give' ),
		'add_new'            => __( 'Add New', 'give' ),
		'add_new_item'       => __( 'Add New Donation', 'give' ),
		'edit_item'          => __( 'Edit Donation', 'give' ),
		'new_item'           => __( 'New Donation', 'give' ),
		'all_items'          => __( 'All Donations', 'give' ),
		'view_item'          => __( 'View Donation', 'give' ),
		'search_items'       => __( 'Search Donations', 'give' ),
		'not_found'          => __( 'No Donations found', 'give' ),
		'not_found_in_trash' => __( 'No Donations found in Trash', 'give' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Donation History', 'give' )
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
 * Get Default Form Labels
 *
 * @since 1.0
 * @return array $defaults Default labels
 */
function give_get_default_form_labels() {
	$defaults = array(
		'singular' => __( 'Form', 'give' ),
		'plural'   => __( 'Forms', 'give' )
	);

	return apply_filters( 'give_default_form_name', $defaults );
}

/**
 * Get Default Campaign Labels
 *
 * @since 1.0
 * @return array $defaults Default labels
 */
function give_get_default_campaign_labels() {
	$defaults = array(
		'singular' => __( 'Campaign', 'give' ),
		'plural'   => __( 'Campaigns', 'give' )
	);

	return apply_filters( 'give_default_campaign_name', $defaults );
}

/**
 * Get Singular Forms Label
 *
 * @since 1.0
 *
 * @param bool $lowercase
 *
 * @return string $defaults['singular'] Singular label
 */
function give_get_forms_label_singular( $lowercase = false ) {
	$defaults = give_get_default_form_labels();

	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Forms Label
 *
 * @since 1.0
 * @return string $defaults['plural'] Plural label
 */
function give_get_forms_label_plural( $lowercase = false ) {
	$defaults = give_get_default_form_labels();

	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Get Singular Campaigns Label
 *
 * @since 1.0
 *
 * @param bool $lowercase
 *
 * @return string $defaults['singular'] Singular label
 */
function give_get_campaigns_label_singular( $lowercase = false ) {
	$defaults = give_get_default_campaign_labels();

	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Campaigns Label
 *
 * @since 1.0
 * @return string $defaults['plural'] Plural label
 */
function give_get_campaigns_label_plural( $lowercase = false ) {
	$defaults = give_get_default_campaign_labels();

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
		$label = give_get_forms_label_singular();
		$title = sprintf( __( 'Enter %s title here', 'give' ), $label );

		return $title;
	}

	$screen = get_current_screen();

	if ( 'give_forms' == $screen->post_type ) {
		$label = give_get_forms_label_singular();
		$title = sprintf( __( 'Enter %s title here', 'give' ), $label );
	}

	return $title;
}

add_filter( 'enter_title_here', 'give_change_default_title' );

/**
 * Registers Custom Post Statuses which are used by the Payments
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
	$url2 = give_get_forms_label_singular();
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


/**
 * Setup Post Type Images
 */
add_action( 'after_setup_theme', 'give_add_thumbnail_support', 10 );
add_action( 'after_setup_theme', 'give_add_image_sizes', 10 );

/**
 * Ensure post thumbnail support is turned on
 */
function give_add_thumbnail_support() {
	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails' );
	}
	add_post_type_support( 'give_forms', 'thumbnail' );
	add_post_type_support( 'give_campaigns', 'thumbnail' );
}

/**
 * Add Give Image sizes to WP
 *
 * @since 1.0
 */
function give_add_image_sizes() {
	$give_form_thumbnail = give_get_image_size( 'give_form_thumbnail' );
	$give_form_single    = give_get_image_size( 'give_form_single' );

	add_image_size( 'give_form_thumbnail', $give_form_thumbnail['width'], $give_form_thumbnail['height'], $give_form_thumbnail['crop'] );
	add_image_size( 'give_form_single', $give_form_single['width'], $give_form_single['height'], $give_form_single['crop'] );
}
