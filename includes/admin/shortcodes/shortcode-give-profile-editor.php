<?php
/**
 * The [give_profile_editor] Shortcode Generator class
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

class Give_Shortcode_Profile_Editor extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['label'] = esc_html__( 'Profile Editor', 'give' );

		parent::__construct( 'give_profile_editor' );
	}

	/**
	 * Define the shortcode attribute fields
	 *
	 * @since 2.5.0
	 * @return array
	 */
	public function define_fields() {
		return array(
			array(
				'type' => 'docs_link',
				'text' => esc_html__( 'Learn more about the Donation Profile Editor Shortcode', 'give' ),
				'link' => 'http://docs.givewp.com/shortcode-profile-editor',
			),
		);
	}
}

new Give_Shortcode_Profile_Editor();
