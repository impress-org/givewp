<?php
/**
 * New Donation Email
 *
 * This class handles all email notification settings.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_New_Donation_Email' ) ) :

	/**
	 * Give_New_Donation_Email
	 *
	 * @abstract
	 * @since       1.8
	 */
	class Give_New_Donation_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @param   mixed[] $objects
		 *
		 * @access  public
		 * @since   1.8
		 */
		public function __construct( $objects = array() ) {
			parent::__construct();

			$this->id          = 'new-donation';
			$this->label       = __( 'New Donation', 'give' );
			$this->description = __( 'Donation Notification will be sent to recipient(s) when new donation received', 'give' );
		}
	}

endif; // End class_exists check

return new Give_New_Donation_Email();