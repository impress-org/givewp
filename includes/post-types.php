<?php
/**
 * Post Type Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and sets up the Donation Forms (give_forms) custom post type
 *
 * @return void
 * @since 1.0
 */
function give_setup_post_types() {

	// Give Forms single post and archive options.
	$give_forms_singular = give_is_setting_enabled( give_get_option( 'forms_singular' ) );
	$give_forms_archives = give_is_setting_enabled( give_get_option( 'forms_archives' ) );

	// Enable/Disable give_forms links if form is saving.
	if ( Give_Admin_Settings::is_saving_settings() ) {
		if ( isset( $_POST['forms_singular'] ) ) {
			$give_forms_singular = give_is_setting_enabled( give_clean( $_POST['forms_singular'] ) );
			flush_rewrite_rules();
		}

		if ( isset( $_POST['forms_archives'] ) ) {
			$give_forms_archives = give_is_setting_enabled( give_clean( $_POST['forms_archives'] ) );
			flush_rewrite_rules();
		}
	}

	$give_forms_slug = defined( 'GIVE_SLUG' ) ? GIVE_SLUG : 'donations';
	// Support for old 'GIVE_FORMS_SLUG' constant
	if ( defined( 'GIVE_FORMS_SLUG' ) ) {
		$give_forms_slug = GIVE_FORMS_SLUG;
	}

	$give_forms_rewrite = defined( 'GIVE_DISABLE_FORMS_REWRITE' ) && GIVE_DISABLE_FORMS_REWRITE ? false : array(
		'slug'       => $give_forms_slug,
		'with_front' => false,
	);

	$give_forms_labels = apply_filters(
		'give_forms_labels',
		array(
			'name'               => __( 'Donation Forms', 'give' ),
			'singular_name'      => __( 'Form', 'give' ),
			'add_new'            => __( 'Add Form', 'give' ),
			'add_new_item'       => __( 'Add New Donation Form', 'give' ),
			'edit_item'          => __( 'Edit Donation Form', 'give' ),
			'new_item'           => __( 'New Form', 'give' ),
			'all_items'          => __( 'All Forms', 'give' ),
			'view_item'          => __( 'View Form', 'give' ),
			'search_items'       => __( 'Search Forms', 'give' ),
			'not_found'          => __( 'No forms found.', 'give' ),
			'not_found_in_trash' => __( 'No forms found in Trash.', 'give' ),
			'parent_item_colon'  => '',
			'menu_name'          => apply_filters( 'give_menu_name', __( 'Donations', 'give' ) ),
			'name_admin_bar'     => apply_filters( 'give_name_admin_bar_name', __( 'Donation Form', 'give' ) ),
		)
	);

	// Default give_forms supports.
	$give_form_supports = array(
		'title',
		'thumbnail',
		'excerpt',
		'revisions',
		'author',
	);

	// Has the user disabled the excerpt?
	if ( ! give_is_setting_enabled( give_get_option( 'forms_excerpt' ) ) ) {
		unset( $give_form_supports[2] );
	}

	// Has user disabled the featured image?
	if ( ! give_is_setting_enabled( give_get_option( 'form_featured_img' ) ) ) {
		unset( $give_form_supports[1] );
		remove_action( 'give_before_single_form_summary', 'give_show_form_images' );
	}

	$give_forms_args = array(
		'labels'          => $give_forms_labels,
		'public'          => $give_forms_singular,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => true,
		'query_var'       => true,
		'rewrite'         => $give_forms_rewrite,
		'map_meta_cap'    => true,
		'capability_type' => 'give_form',
		'has_archive'     => $give_forms_archives,
		'menu_icon'       => 'dashicons-give',
		'hierarchical'    => false,
		'supports'        => apply_filters( 'give_forms_supports', $give_form_supports ),
	);
	register_post_type( 'give_forms', apply_filters( 'give_forms_post_type_args', $give_forms_args ) );

	/** Donation Post Type */
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
		'not_found'          => __( 'No donations found.', 'give' ),
		'not_found_in_trash' => __( 'No donations found in Trash.', 'give' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Donations', 'give' ),
	);

	$payment_args = array(
		'labels'          => apply_filters( 'give_payment_labels', $payment_labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'map_meta_cap'    => true,
		'capability_type' => 'give_payment',
		'supports'        => array( 'title' ),
		'can_export'      => true,
	);
	register_post_type( 'give_payment', $payment_args );

}

add_action( 'init', 'give_setup_post_types', 1 );


/**
 * Give Setup Taxonomies
 *
 * Registers the custom taxonomies for the give_forms custom post type
 *
 * @return void
 * @since      1.0
 */
function give_setup_taxonomies() {

	$slug = defined( 'GIVE_FORMS_SLUG' ) ? GIVE_FORMS_SLUG : 'donations';

	/** Categories */
	$category_labels = array(
		'name'              => _x( 'Form Categories', 'taxonomy general name', 'give' ),
		'singular_name'     => _x( 'Category', 'taxonomy singular name', 'give' ),
		'search_items'      => __( 'Search Categories', 'give' ),
		'all_items'         => __( 'All Categories', 'give' ),
		'parent_item'       => __( 'Parent Category', 'give' ),
		'parent_item_colon' => __( 'Parent Category:', 'give' ),
		'edit_item'         => __( 'Edit Category', 'give' ),
		'update_item'       => __( 'Update Category', 'give' ),
		'add_new_item'      => __( 'Add New Category', 'give' ),
		'new_item_name'     => __( 'New Category Name', 'give' ),
		'menu_name'         => __( 'Categories', 'give' ),
	);

	$category_args = apply_filters(
		'give_forms_category_args',
		array(
			'hierarchical' => true,
			'labels'       => apply_filters( 'give_forms_category_labels', $category_labels ),
			'show_ui'      => true,
			'query_var'    => 'give_forms_category',
			'rewrite'      => array(
				'slug'         => $slug . '/category',
				'with_front'   => false,
				'hierarchical' => true,
			),
			'capabilities' => array(
				'manage_terms' => 'manage_give_form_terms',
				'edit_terms'   => 'edit_give_form_terms',
				'assign_terms' => 'assign_give_form_terms',
				'delete_terms' => 'delete_give_form_terms',
			),
		)
	);

	/** Tags */
	$tag_labels = array(
		'name'                  => _x( 'Form Tags', 'taxonomy general name', 'give' ),
		'singular_name'         => _x( 'Tag', 'taxonomy singular name', 'give' ),
		'search_items'          => __( 'Search Tags', 'give' ),
		'all_items'             => __( 'All Tags', 'give' ),
		'parent_item'           => __( 'Parent Tag', 'give' ),
		'parent_item_colon'     => __( 'Parent Tag:', 'give' ),
		'edit_item'             => __( 'Edit Tag', 'give' ),
		'update_item'           => __( 'Update Tag', 'give' ),
		'add_new_item'          => __( 'Add New Tag', 'give' ),
		'new_item_name'         => __( 'New Tag Name', 'give' ),
		'menu_name'             => __( 'Tags', 'give' ),
		'choose_from_most_used' => __( 'Choose from most used tags.', 'give' ),
	);

	$tag_args = apply_filters(
		'give_forms_tag_args',
		array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'give_forms_tag_labels', $tag_labels ),
			'show_ui'      => true,
			'query_var'    => 'give_forms_tag',
			'rewrite'      => array(
				'slug'         => $slug . '/tag',
				'with_front'   => false,
				'hierarchical' => true,
			),
			'capabilities' => array(
				'manage_terms' => 'manage_give_form_terms',
				'edit_terms'   => 'edit_give_form_terms',
				'assign_terms' => 'assign_give_form_terms',
				'delete_terms' => 'delete_give_form_terms',
			),
		)
	);

	// Does the user want category?
	$enable_category = give_is_setting_enabled( give_get_option( 'categories', 'disabled' ) );

	// Does the user want tag?
	$enable_tag = give_is_setting_enabled( give_get_option( 'tags', 'disabled' ) );

	// Enable/Disable category and tag if form is saving.
	if ( Give_Admin_Settings::is_saving_settings() ) {
		if ( isset( $_POST['categories'] ) ) {
			$enable_category = give_is_setting_enabled( give_clean( $_POST['categories'] ) );
			flush_rewrite_rules();
		}

		if ( isset( $_POST['tags'] ) ) {
			$enable_tag = give_is_setting_enabled( give_clean( $_POST['tags'] ) );
			flush_rewrite_rules();
		}
	}

	if ( $enable_category ) {
		register_taxonomy( 'give_forms_category', array( 'give_forms' ), $category_args );
		register_taxonomy_for_object_type( 'give_forms_category', 'give_forms' );
	}

	if ( $enable_tag ) {
		register_taxonomy( 'give_forms_tag', array( 'give_forms' ), $tag_args );
		register_taxonomy_for_object_type( 'give_forms_tag', 'give_forms' );
	}
}

add_action( 'init', 'give_setup_taxonomies', 0 );


/**
 * Get Default Form Labels
 *
 * @return array $defaults Default labels
 * @since 1.0
 */
function give_get_default_form_labels() {
	$defaults = array(
		'singular' => __( 'Form', 'give' ),
		'plural'   => __( 'Forms', 'give' ),
	);

	return apply_filters( 'give_default_form_name', $defaults );
}

/**
 * Get Singular Forms Label
 *
 * @param bool $lowercase
 *
 * @return string $defaults['singular'] Singular label
 * @since 1.0
 */
function give_get_forms_label_singular( $lowercase = false ) {
	$defaults = give_get_default_form_labels();

	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Forms Label
 *
 * @return string $defaults['plural'] Plural label
 * @since 1.0
 */
function give_get_forms_label_plural( $lowercase = false ) {
	$defaults = give_get_default_form_labels();

	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Change default "Enter title here" input
 *
 * @param string $title Default title placeholder text
 *
 * @return string $title New placeholder text
 * @since 1.0
 */
function give_change_default_title( $title ) {
	// If a frontend plugin uses this filter (check extensions before changing this function)
	if ( ! is_admin() ) {
		$title = __( 'Enter form title here', 'give' );

		return $title;
	}

	$screen = get_current_screen();

	if ( 'give_forms' == $screen->post_type ) {
		$title = __( 'Enter form title here', 'give' );
	}

	return $title;
}

add_filter( 'enter_title_here', 'give_change_default_title' );

/**
 * Registers Custom Post Statuses which are used by the Payments
 *
 * @return void
 * @since 1.0
 */
function give_register_post_type_statuses() {
	// Payment Statuses
	register_post_status(
		'refunded',
		array(
			'label'                     => __( 'Refunded', 'give' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'give' ),
		)
	);
	register_post_status(
		'failed',
		array(
			'label'                     => __( 'Failed', 'give' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'give' ),
		)
	);
	register_post_status(
		'revoked',
		array(
			'label'                     => __( 'Revoked', 'give' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Revoked <span class="count">(%s)</span>', 'Revoked <span class="count">(%s)</span>', 'give' ),
		)
	);
	register_post_status(
		'cancelled',
		array(
			'label'                     => __( 'Cancelled', 'give' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'give' ),
		)
	);
	register_post_status(
		'abandoned',
		array(
			'label'                     => __( 'Abandoned', 'give' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'give' ),
		)
	);
	register_post_status(
		'processing',
		array(
			'label'                     => _x( 'Processing', 'Processing payment status', 'give' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'give' ),
		)
	);

	register_post_status(
		'preapproval',
		array(
			'label'                     => _x( 'Preapproval', 'Preapproval payment status', 'give' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Preapproval <span class="count">(%s)</span>', 'Preapproval <span class="count">(%s)</span>', 'give' ),
		)
	);

}

add_action( 'init', 'give_register_post_type_statuses' );

/**
 * Updated Messages
 *
 * Returns an array of with all updated messages.
 *
 * @param array $messages Post updated message
 *
 * @return array $messages New post updated messages
 * @since 1.0
 */
function give_updated_messages( $messages ) {
	global $post, $post_ID;

	if ( ! give_is_setting_enabled( give_get_option( 'forms_singular' ) ) ) {

		$messages['give_forms'] = array(
			1 => __( 'Form updated.', 'give' ),
			4 => __( 'Form updated.', 'give' ),
			6 => __( 'Form published.', 'give' ),
			7 => __( 'Form saved.', 'give' ),
			8 => __( 'Form submitted.', 'give' ),
		);

	} else {

		$messages['give_forms'] = array(
			1 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form updated.', 'give' ), get_permalink( $post_ID ), __( 'View Form', 'give' ) ),
			4 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form updated.', 'give' ), get_permalink( $post_ID ), __( 'View Form', 'give' ) ),
			6 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form published.', 'give' ), get_permalink( $post_ID ), __( 'View Form', 'give' ) ),
			7 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form saved.', 'give' ), get_permalink( $post_ID ), __( 'View Form', 'give' ) ),
			8 => sprintf( '%1$s <a href="%2$s">%3$s</a>', __( 'Form submitted.', 'give' ), get_permalink( $post_ID ), __( 'View Form', 'give' ) ),
		);

	}

	return $messages;
}

add_filter( 'post_updated_messages', 'give_updated_messages' );

/**
 * Ensure post thumbnail support is turned on
 */
function give_add_thumbnail_support() {
	if ( ! give_is_setting_enabled( give_get_option( 'form_featured_img' ) ) ) {
		return;
	}

	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails' );
	}

	add_post_type_support( 'give_forms', 'thumbnail' );
}

add_action( 'after_setup_theme', 'give_add_thumbnail_support', 10 );

/**
 * Give Sidebars
 *
 * This option adds Give sidebars; registered late so it display last in list
 */
function give_widgets_init() {

	// Single Give Forms (disabled if single turned off in settings)
	if (
		give_is_setting_enabled( give_get_option( 'forms_singular' ) )
		&& give_is_setting_enabled( give_get_option( 'form_sidebar' ) )
	) {

		register_sidebar(
			apply_filters(
				'give_forms_single_sidebar',
				array(
					'name'          => __( 'GiveWP Single Form Sidebar', 'give' ),
					'id'            => 'give-forms-sidebar',
					'description'   => __( 'Widgets in this area will be shown on the single GiveWP forms aside area. This sidebar will not display for embedded forms.', 'give' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h3 class="widgettitle widget-title">',
					'after_title'   => '</h3>',
				)
			)
		);

	}
}

add_action( 'widgets_init', 'give_widgets_init', 999 );


/**
 * Remove "Quick Edit" for the give_forms CPT.
 *
 * @param array $actions
 * @param null  $post
 *
 * @return array
 * @since 2.3.0
 */
function give_forms_disable_quick_edit( $actions = array(), $post = null ) {

	// Abort if the post type is not "give_forms".
	if ( ! is_post_type_archive( 'give_forms' ) ) {
		return $actions;
	}

	// Remove the Quick Edit link.
	if ( isset( $actions['inline hide-if-no-js'] ) ) {
		unset( $actions['inline hide-if-no-js'] );
	}

	// Return the set of links without Quick Edit.
	return $actions;

}

add_filter( 'post_row_actions', 'give_forms_disable_quick_edit', 10, 2 );

/**
 * Removes the screen options pull down. It is reset later in a different position.
 *
 * @param bool      $display_boolean  Whether to display screen options.
 * @param WP_Screen $wp_screen_object The screen object.
 *
 * @return bool Whether to display screen options.
 * @since 2.5.0
 */
function give_remove_screen_options( $display_boolean, $wp_screen_object ) {

	if ( false !== strpos( $wp_screen_object->id, 'give' ) ) {
		return false;
	}

	// Don't mess with other screens.
	return $display_boolean;
}

// add_filter( 'screen_options_show_screen', 'give_remove_screen_options', 10, 2 );

/**
 * Renders the screen options back after admin bar to ensure it pushes down the banner rather than overlaps them as is default in WordPress.
 *
 * @since  2.5.0
 */
function give_render_screen_options() {
	if ( ! is_admin() ) {
		return;
	}

	$current_screen = get_current_screen();

	if ( empty( $current_screen ) ) {
		return;
	}

	if ( false !== strpos( $current_screen->id, 'give' ) ) {
		// Render Screen Options above the banner.
		$current_screen->render_screen_meta();
	}
}

add_action( 'wp_after_admin_bar_render', 'give_render_screen_options' );
