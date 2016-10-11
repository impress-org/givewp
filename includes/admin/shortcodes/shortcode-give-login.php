<?php
/**
 * The [give_login] Shortcode Generator class
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

class Give_Shortcode_Login extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Login', 'give' );
		$this->shortcode['label'] = esc_html__( 'Login', 'give' );

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
				'html' => sprintf( '<p class="no-margin">%s</p>', esc_html__( 'Login Redirect URL (optional):', 'give' ) ),
			),
			array(
				'type'     => 'textbox',
				'name'     => 'login-redirect',
				'minWidth' => 320,
				'tooltip'  => esc_attr__( 'Enter an URL here to redirect to after login.', 'give' ),
			),
            array(
                'type' => 'container',
                'html' => sprintf( '<p class="no-margin">%s</p>', esc_html__( 'Logout Redirect URL (optional):', 'give' ) ),
            ),
            array(
                'type'     => 'textbox',
                'name'     => 'logout-redirect',
                'minWidth' => 320,
                'tooltip'  => esc_attr__( 'Enter an URL here to redirect to after logout.', 'give' ),
            ),
		);
	}
}

new Give_Shortcode_Login;
