<?php
/**
 * The [give_goal] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

defined( 'ABSPATH' ) or exit;

class Give_Shortcode_Donation_Form_Goal extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title']   = __( 'Donation Form Goal', 'give' );
		$this->shortcode['label']   = __( 'Donation Form Goal', 'give' );
		$this->shortcode['require'] = array( 'id' );
		$this->shortcode['alert']   = __( 'You must first select a Form!', 'give' );

		parent::__construct( 'give_goal' );
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
				'name'        => 'id',
				'tooltip'     => __( 'Select a Donation Form', 'give' ),
				'placeholder' => sprintf( '– %s –', __( 'Select a Form', 'give' ) ),
			),
			array(
				'type' => 'container',
				'html' => sprintf( '<p style="font-weight: 600 !important; margin-top: 1em;">%s</p>', __( 'Optional settings', 'give' ) ),
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
