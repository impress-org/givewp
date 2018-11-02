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
                'slug' => 'give',
                'title' => __( 'Give', 'give' ),
            ),
        )
    );
}
add_filter( 'block_categories', 'give_blocks_category', 10, 2 );

/**
* Blocks
*/
require_once GIVE_PLUGIN_DIR . 'blocks/donation-form/class-give-donation-form-block.php';
require_once GIVE_PLUGIN_DIR . 'blocks/donation-form-grid/class-give-donation-form-grid-block.php';
require_once GIVE_PLUGIN_DIR . 'blocks/donor-wall/class-give-donor-wall.php';
