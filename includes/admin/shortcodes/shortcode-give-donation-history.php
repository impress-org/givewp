<?php
/**
 * The [donation_history] Shortcode Generator class
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

class Give_Shortcode_Donation_History extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['label'] = esc_html__( 'Donation History', 'give' );

		parent::__construct( 'donation_history' );
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
				'text' => esc_html__( 'Learn more about the Donation History Shortcode', 'give' ),
				'link' => 'http://docs.givewp.com/shortcode-donation-history',
			),
		);
	}
}

new Give_Shortcode_Donation_History;
