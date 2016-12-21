<?php
/**
 * The [give_form] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Shortcode_Donation_Form
 */
class Give_Shortcode_Donation_Form extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title']   = esc_html__( 'Donation Form', 'give' );
		$this->shortcode['label']   = esc_html__( 'Donation Form', 'give' );

		parent::__construct( 'give_form' );
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
				'name'        => 'id',
				'tooltip'     => esc_attr__( 'Select a Donation Form', 'give' ),
				'placeholder' => '- ' . esc_attr__( 'Select a Donation Form', 'give' ) . ' -',
				'required'    => array(
					'alert' => esc_html__( 'You must first select a Form!', 'give' ),
					'error' => sprintf( '<p class="strong">%s</p><p class="no-margin">%s</p>', esc_html__( 'No forms found.', 'give' ), $create_form_link ),
				),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="strong margin-top">%s</p>', esc_html__( 'Optional settings', 'give' ) ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_title',
				'label'   => esc_attr__( 'Show Title:', 'give' ),
				'tooltip' => esc_attr__( 'Do you want to display the form title?', 'give' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_goal',
				'label'   => esc_attr__( 'Show Goal:', 'give' ),
				'tooltip' => esc_attr__( 'Do you want to display the donation goal?', 'give' ),
				'options' => array(
					'true'  => esc_html__( 'Show', 'give' ),
					'false' => esc_html__( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_content',
				'minWidth' => 240,
				'label'   => esc_attr__( 'Display Content:', 'give' ),
				'tooltip' => esc_attr__( 'Do you want to display the form content?', 'give' ),
				'options' => array(
					'none'  => esc_html__( 'No Content', 'give' ),
					'above' => esc_html__( 'Display content ABOVE the fields', 'give' ),
					'below' => esc_html__( 'Display content BELOW the fields', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'display_style',
				'label'   => esc_attr__( 'Donation Fields:', 'give' ),
				'tooltip' => esc_attr__( 'How would you like to display donation information?', 'give' ),
				'options' => array(
					'onpage' => esc_html__( 'Show on Page', 'give' ),
					'reveal' => esc_html__( 'Reveal Upon Click', 'give' ),
					'modal'  => esc_html__( 'Modal Window Upon Click', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'float_labels',
				'label'   => esc_attr__( 'Floating Labels:', 'give' ),
				'tooltip' => esc_attr__( 'Override the default floating labels setting for this form.', 'give' ),
				'options' => array(
					'enabled'  => esc_html__( 'Enabled', 'give' ),
					'disabled' => esc_html__( 'Disabled', 'give' ),
				),
			),
		);
	}
}

new Give_Shortcode_Donation_Form;
