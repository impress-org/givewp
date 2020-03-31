<?php
/**
 * The [give_register] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Shortcode_Register extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['title'] = esc_html__( 'Register', 'give' );
		$this->shortcode['label'] = esc_html__( 'Register', 'give' );

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
				'html' => sprintf( '<p class="no-margin">%s</p>', esc_html__( 'Redirect URL (optional):', 'give' ) ),
			),
			array(
				'type'     => 'textbox',
				'name'     => 'redirect',
				'minWidth' => 320,
				'tooltip'  => esc_attr__( 'Enter an URL here to redirect to after registering.', 'give' ),
			),
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Register Shortcode', 'give' ),
				'link' => 'http://docs.givewp.com/shortcode-give-register',
			),
		);
	}
}

new Give_Shortcode_Register();
