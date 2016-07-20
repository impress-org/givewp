<?php
/**
 * The [give_login] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

defined( 'ABSPATH' ) or exit;

class Give_Shortcode_Login extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html( 'Login', 'give' );
		$this->shortcode['label'] = esc_html( 'Login', 'give' );

		parent::__construct( 'give_login' );
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
				'html' => sprintf( '<p class="no-margin">%s</p>', esc_html( 'Redirect URL (optional):', 'give' ) ),
			),
			array(
				'type'     => 'textbox',
				'name'     => 'redirect',
				'minWidth' => 320,
				'tooltip'  => esc_attr( 'Enter an URL here to redirect to after login.', 'give' ),
			),
		);
	}
}

new Give_Shortcode_Login;
