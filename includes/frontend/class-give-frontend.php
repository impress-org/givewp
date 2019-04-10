<?php

/**
 * This class will handle file loading for frontend.
 *
 * @package     Give
 * @subpackage  Frontend
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */
class Give_Frontend {
	/**
	 * Instance.
	 *
	 * @since  2.4.0
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.4.0
	 * @access public
	 * @return Give_Frontend
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup Admin
	 *
	 * @sinve  2.4.0
	 * @access private
	 */
	private function setup() {
		$this->frontend_loading();

		add_action( 'give_init', array( $this, 'bc_240' ), 0 );
	}

	/**
	 *  Load core file
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function frontend_loading() {
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-template-loader.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-email-access.php'; // @todo: [refactor] can be load only for success and history page.
	}

	/**
	 * Backward compatibility GIVE_VERSION < 2.4.0
	 *
	 * @since 2.4.0
	 * @ccess public
	 *
	 * @param Give $give
	 */
	public function bc_240( $give ) {
		$give->template_loader = new Give_Template_Loader();
		$give->email_access    = new Give_Email_Access();
	}
}

Give_Frontend::get_instance();
