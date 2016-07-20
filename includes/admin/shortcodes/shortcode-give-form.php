<?php
/**
 * The [give_form] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Class Give_Shortcode_Donation_Form
 */
class Give_Shortcode_Donation_Form extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title']   = esc_html( 'Donation Form', 'give' );
		$this->shortcode['label']   = esc_html( 'Donation Form', 'give' );

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
				'tooltip'     => esc_attr( 'Select a Donation Form', 'give' ),
				'placeholder' => esc_attr( '- Select a Form -', 'give' ),
				'required'    => array(
					'alert' => esc_html( 'You must first select a Form!', 'give' ),
					'error' => sprintf( '<p class="strong">%s</p><p class="no-margin">%s</p>', esc_html( 'No donation forms were found!', 'give' ), $create_form_link ),
				),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="strong margin-top">%s</p>', esc_html( 'Optional form settings', 'give' ) ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_title',
				'label'   => esc_html( 'Show Title:', 'give' ),
				'tooltip' => esc_attr( 'Do you want to display the form title?', 'give' ),
				'options' => array(
					'true'  => esc_html( 'Show', 'give' ),
					'false' => esc_html( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_goal',
				'label'   => esc_html( 'Show Goal:', 'give' ),
				'tooltip' => esc_attr( 'Do you want to display the donation goal?', 'give' ),
				'options' => array(
					'true'  => esc_html( 'Show', 'give' ),
					'false' => esc_html( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_content',
				'minWidth' => 240,
				'label'   => esc_html( 'Display Content:', 'give' ),
				'tooltip' => esc_attr( 'Do you want to display the form content?', 'give' ),
				'options' => array(
					'none'  => esc_html( 'No Content', 'give' ),
					'above' => esc_html( 'Display above the form fields', 'give' ),
					'below' => esc_html( 'Display below the form fields', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'display_style',
				'label'   => esc_html( 'Payment Fields:', 'give' ),
				'tooltip' => esc_attr( 'How would you like to display payment information?', 'give' ),
				'options' => array(
					'onpage' => esc_html( 'Show on Page', 'give' ),
					'reveal' => esc_html( 'Reveal Upon Click', 'give' ),
					'modal'  => esc_html( 'Modal Window Upon Click', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'float_labels',
				'label'   => esc_html( 'Floating Labels:', 'give' ),
				'tooltip' => esc_attr( 'Override the default floating labels setting for this form?', 'give' ),
				'options' => array(
					'enabled'  => esc_html( 'Enabled', 'give' ),
					'disabled' => esc_html( 'Disabled', 'give' ),
				),
			),
		);
	}
}

new Give_Shortcode_Donation_Form;
