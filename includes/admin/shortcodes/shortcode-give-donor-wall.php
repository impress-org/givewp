<?php
/**
 * The [give_donor_grid] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, WordImpress
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
				'tooltip'     => esc_attr__( 'Select a Donation Form', 'give' ),
				'placeholder' => '- ' . esc_attr__( 'Select a Donation Form', 'give' ) . ' -',
			),
			array(
				'type'        => 'textbox',
				'name'        => 'donors_per_page',
				'label'       => esc_attr__( 'Donors Per Page', 'give' ),
				'placeholder' => '20',
			),
			array(
				'type'        => 'textbox',
				'name'        => 'comment_length',
				'label'       => esc_attr__( 'Comment Length', 'give' ),
				'placeholder' => '20',
			),
			array(
				'type'        => 'textbox',
				'name'        => 'readmore_text',
				'label'       => esc_attr__( 'Read More Text', 'give' ),
				'placeholder' => esc_html__( 'Read More', 'give' ),
			),
			array(
				'type'        => 'textbox',
				'name'        => 'loadmore_text',
				'label'       => esc_attr__( 'Load More Text', 'give' ),
				'placeholder' => esc_html__( 'Load More', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'columns',
				'label'       => esc_attr__( 'Columns:', 'give' ),
				'tooltip'     => esc_attr__( 'Sets the number of forms per row.', 'give' ),
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
				'name'        => 'show_avatar',
				'label'       => esc_attr__( 'Show Avatar', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_name',
				'label'       => esc_attr__( 'Show Name', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_total',
				'label'       => esc_attr__( 'Show Total', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_time',
				'label'       => esc_attr__( 'Show Date', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			),
			array(
				'type'        => 'listbox',
				'name'        => 'show_comments',
				'label'       => esc_attr__( 'Show Comments', 'give' ),
				'options'     => array(
					'false' => esc_html__( 'Hide', 'give' ),
				),
				'placeholder' => esc_html__( 'Show', 'give' ),
			)
		);
	}
}

new Give_Shortcode_Donor_Wall();
