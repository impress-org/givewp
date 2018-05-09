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

		parent::__construct( 'give_donor_grid' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @return array
	 */
	public function define_fields() {
		$create_form_link = sprintf(
		/* translators: %s: create new form URL */
			__( '<a href="%s">Create</a> a new Donation Form.', 'give' ),
			admin_url( 'post-new.php?post_type=give_forms' )
		);

		return array(
			array(
				'type'        => 'post',
				'query_args'  => array(
					'post_type' => 'give_forms',
				),
				'name'        => 'give_forms',
				'tooltip'     => esc_attr__( 'Select a Donation Form', 'give' ),
				'placeholder' => '- ' . esc_attr__( 'Select a Donation Form', 'give' ) . ' -',
				'required'    => array(
					'alert' => esc_html__( 'You must first select a Form!', 'give' ),
					'error' => sprintf( '<p class="strong">%s</p><p class="no-margin">%s</p>', esc_html__( 'No forms found.', 'give' ), $create_form_link ),
				),
			),
			array(
				'type'    => 'textbox',
				'classes' => 'give-hidden give-donors-per-page',
				'name'    => 'donors_per_page',
				'label'   => esc_attr__( 'Donors Per Page', 'give' ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_avatar',
				'label'   => esc_attr__( 'Show Avatar', 'give' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_name',
				'label'   => esc_attr__( 'Show Name', 'give' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_total',
				'label'   => esc_attr__( 'Show Total', 'give' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_time',
				'label'   => esc_attr__( 'Show Time', 'give' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_comments',
				'label'   => esc_attr__( 'Show Comments', 'give' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				),
			),
		);
	}
}

new Give_Shortcode_Donor_Wall();
