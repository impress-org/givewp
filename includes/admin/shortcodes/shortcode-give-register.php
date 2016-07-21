<?php
/**
 * The [give_register] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

defined( 'ABSPATH' ) or exit;

class Give_Shortcode_Register extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = __( 'Register', 'give' );
		$this->shortcode['label'] = __( 'Register', 'give' );

		parent::__construct( 'give_register' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @return array
	 */
	public function define_fields() {

		return array(
			array(
				'type' => 'container',
				'html' => sprintf( '<p class="no-margin">%s</p>', __( 'Redirect URL (optional):', 'give' ) ),
			),
			array(
				'type'     => 'textbox',
				'name'     => 'redirect',
				'minWidth' => 320,
				'tooltip'  => __( 'Enter an URL here to redirect to after registering.', 'give' ),
			),
		);
	}
}

new Give_Shortcode_Register;
