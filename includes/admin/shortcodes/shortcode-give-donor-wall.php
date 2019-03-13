<?php
/**
 * The [give_donor_grid] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Shortcode_Donor_Wall
 */
class Give_Shortcode_Donor_Wall extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Donor Wall', 'give' );
		$this->shortcode['label'] = esc_html__( 'Donor Wall', 'give' );

		parent::__construct( 'give_donor_wall' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @return array
	 */
	public function define_fields() {
		return array(
			array(
				'type'        => 'post',
				'query_args'  => array(
					'post_type' => 'give_forms',
				),
				'name'        => 'form_id',
				'label'       => esc_attr__( 'Form:', 'give' ),
				'tooltip'     => esc_attr__( 'Filters donors by form. By default, all donations except for anonymous donations are displayed.', 'give' ),
				'placeholder' => esc_attr__( 'All Forms', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'orderby',
				'label'       => esc_attr__( 'Order By:', 'give' ),
				'tooltip'     => esc_attr__( 'Different parameters to set the order in which donors appear.', 'give' ),
				'options'     => array(
					'donation_amount' => esc_html__( 'Donation Amount', 'give' ),
				),
				'placeholder' => esc_html__( 'Date Created', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'order',
				'label'       => esc_attr__( 'Order:', 'give' ),
				'tooltip'     => esc_attr__( 'Sets the order in which donors appear.', 'give' ),
				'options'     => array(
					'ASC' => esc_html__( 'Ascending', 'give' ),
				),
				'placeholder' => esc_html__( 'Descending', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'only_comments',
				'label'       => esc_attr__( 'Donors:', 'give' ),
				'tooltip'     => esc_attr__( 'Determines whether to display all donors or only donors with comments.', 'give' ),
				'options'     => array(
					'true' => esc_html__( 'Donors with Comments', 'give' ),
				),
				'placeholder' => esc_html__( 'All Donors', 'give' ),
			),
			array(
				'type'        => 'textbox',
				'name'        => 'donors_per_page',
				'label'       => esc_attr__( 'Donors Per Page:', 'give' ),
				'tooltip'     => esc_attr__( 'Sets the number of donors per page.', 'give' ),
				'placeholder' => '12',
			),
			array(
				'type'        => 'textbox',
				'name'        => 'comment_length',
				'label'       => esc_attr__( 'Comment Length:', 'give' ),
				'tooltip'     => esc_attr__( 'Sets the number of characters to display before the comment is truncated.', 'give' ),
				'placeholder' => '140',
			),
			array(
				'type'        => 'textbox',
				'name'        => 'readmore_text',
				'label'       => esc_attr__( 'Read More Text:', 'give' ),
				'tooltip'     => esc_attr__( 'Defines the text that appears if a comment is truncated.', 'give' ),
				'placeholder' => esc_html__( 'Read more', 'give' ),
			),
			array(
				'type'        => 'textbox',
				'name'        => 'loadmore_text',
				'label'       => esc_attr__( 'Load More Text:', 'give' ),
				'tooltip'     => esc_attr__( 'Defines the button text used for pagination.', 'give' ),
				'placeholder' => esc_html__( 'Load more', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'columns',
				'label'       => esc_attr__( 'Columns:', 'give' ),
				'tooltip'     => esc_attr__( 'Sets the number of donors per row.', 'give' ),
				'options'     => array(
					'1' => esc_html__( '1', 'give' ),
					'2' => esc_html__( '2', 'give' ),
					'3' => esc_html__( '3', 'give' ),
					'4' => esc_html__( '4', 'give' ),
				),
				'placeholder' => esc_html__( 'Best Fit', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'anonymous',
				'label'       => esc_attr__( 'Anonymous:', 'give' ),
				'tooltip'     => esc_attr__( 'Determines whether anonymous donations are included.', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_avatar',
				'label'       => esc_attr__( 'Donor Avatar:', 'give' ),
				'tooltip'     => esc_attr__( 'Determines whether the avatar is visible.', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_name',
				'label'       => esc_attr__( 'Donor Name:', 'give' ),
				'tooltip'     => esc_attr__( 'Determines whether the name is visible.', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_total',
				'label'       => esc_attr__( 'Donation Total:', 'give' ),
				'tooltip'     => esc_attr__( 'Determines whether the donation total is visible.', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_time',
				'label'       => esc_attr__( 'Donation Date:', 'give' ),
				'tooltip'     => esc_attr__( 'Determines whether the date of the donation is visible.', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_comments',
				'label'       => esc_attr__( 'Donor Comment:', 'give' ),
				'tooltip'     => esc_attr__( 'Determines whether the comment is visible.', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donor Wall Shortcode', 'give' ),
				'link' => 'http://docs.givewp.com/shortcode-donor-wall',
			),
		);
	}
}

new Give_Shortcode_Donor_Wall();
