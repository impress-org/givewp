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

		$this->shortcode['title'] = __( 'Donation Form Goal', 'give' );
		$this->shortcode['label'] = __( 'Donation Form Goal', 'give' );

		parent::__construct( 'give_goal' );
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
				'html' => sprintf( '<p class="strong margin-top">%s</p>', __( 'Optional settings', 'give' ) ),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_text',
				'label'   => __( 'Show Text:', 'give' ),
				'tooltip' => __( 'This text displays the amount of income raised compared to the goal.', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_bar',
				'label'   => __( 'Show Progress Bar:', 'give' ),
				'tooltip' => __( 'Do you want to display the goal\'s progress bar?', 'give' ),
				'options' => array(
					'true'  => __( 'Show', 'give' ),
					'false' => __( 'Hide', 'give' ),
				),
			),
		);
	}
}

new Give_Shortcode_Donation_Form_Goal;