<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Custom Block Category for Give blocks
 */
function give_blocks_category( $categories, $post ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'give',
				'title' => __( 'Give', 'give' ),
			),
		)
	);
}

/**
 * @unreleased The `block_categories` filter is deprecated as of WordPress 5.8
 */
if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
	add_filter( 'block_categories_all', 'give_blocks_category', 10, 2 );
} else {
	add_filter( 'block_categories', 'give_blocks_category', 10, 2 );
}

/**
* Blocks
*/
require_once GIVE_PLUGIN_DIR . 'blocks/donation-form/class-give-donation-form-block.php';
require_once GIVE_PLUGIN_DIR . 'blocks/donation-form-grid/class-give-donation-form-grid-block.php';
require_once GIVE_PLUGIN_DIR . 'blocks/donor-wall/class-give-donor-wall.php';
