<?php
/**
 * The [give_goal] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

defined( 'ABSPATH' ) or exit;

class Give_Shortcode_Donation_Form_Goal extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html( 'Donation Form Goal', 'give' );
		$this->shortcode['label'] = esc_html( 'Donation Form Goal', 'give' );

		parent::__construct( 'give_goal' );
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
				'html' => sprintf( '<p class="strong margin-top">%s</p>', esc_html( 'Optional settings', 'give' ) ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_text',
				'label'   => esc_html( 'Show Text:', 'give' ),
				'tooltip' => esc_attr( 'This text displays the amount of income raised compared to the goal.', 'give' ),
				'options' => array(
					'true'  => esc_html( 'Show', 'give' ),
					'false' => esc_html( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_bar',
				'label'   => esc_html( 'Show Progress Bar:', 'give' ),
				'tooltip' => esc_attr( 'Do you want to display the goal\'s progress bar?', 'give' ),
				'options' => array(
					'true'  => esc_html( 'Show', 'give' ),
					'false' => esc_html( 'Hide', 'give' ),
				),
			),
		);
	}
}

new Give_Shortcode_Donation_Form_Goal;