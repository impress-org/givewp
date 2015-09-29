<?php
/**
 * The [donation_history] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

defined( 'ABSPATH' ) or exit;

class Give_Shortcode_Donation_History extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['label'] = __( 'Donation History', 'give' );

		parent::__construct( 'donation_history' );
	}
}

new Give_Shortcode_Donation_History;
