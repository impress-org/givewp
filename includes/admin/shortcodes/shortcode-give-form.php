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

class Give_Shortcode_Donation_Form extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title']   = __( 'Donation Form', 'give' );
		$this->shortcode['label']   = __( 'Donation Form', 'give' );

		parent::__construct( 'give_form' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @return array
	 */
	public function define_fields() {

		$create_form_link = sprintf( __( '%sClick here%s to create a new Donation Form.', 'give' ),
			'<a href="' . admin_url( 'post-new.php?post_type=give_forms' ) . '">',
			'</a>'
		);

		return array(
			array(
				'type'        => 'post',
				'query_args'  => array(
					'post_type' => 'give_forms',
				),
				'name'        => 'id',
				'tooltip'     => __( 'Select a Donation Form', 'give' ),
				'placeholder' => sprintf( '– %s –', __( 'Select a Form', 'give' ) ),
				'required'    => array(
					'alert' => __( 'You must first select a Form!', 'give' ),
					'error' => sprintf( '<p class="strong">%s</p><p class="no-margin">%s</p>', __( 'No donation forms were found!', 'give' ), $create_form_link ),
				),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="strong margin-top">%s</p>', __( 'Optional form settings', 'give' ) ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_title',
				'label'   => __( 'Show Title:', 'give' ),
				'tooltip' => __( 'Do you want to display the form title?', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_goal',
				'label'   => __( 'Show Goal:', 'give' ),
				'tooltip' => __( 'Do you want to display the donation goal?', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_content',
				'minWidth' => 240,
				'label'   => __( 'Display Content:', 'give' ),
				'tooltip' => __( 'Do you want to display the form content?', 'give' ),
				'options' => array(
					'none'  => __( 'No Content', 'give' ),
					'above' => __( 'Display above the form fields', 'give' ),
					'below' => __( 'Display below the form fields', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'display_style',
				'label'   => __( 'Payment Fields:', 'give' ),
				'tooltip' => __( 'How would you like to display payment information?', 'give' ),
				'options' => array(
					'onpage' => __( 'Show on Page', 'give' ),
					'reveal' => __( 'Reveal Upon Click', 'give' ),
					'modal'  => __( 'Modal Window Upon Click', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'float_labels',
				'label'   => __( 'Floating Labels:', 'give' ),
				'tooltip' => __( 'Override the default floating labels setting for this form?', 'give' ),
				'options' => array(
					'enabled'  => __( 'Enabled', 'give' ),
					'disabled' => __( 'Disabled', 'give' ),
				),
			),
		);
	}
}

new Give_Shortcode_Donation_Form;
