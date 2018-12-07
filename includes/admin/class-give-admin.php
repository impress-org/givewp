<?php
/**
 * This class will handle file loading for admin.
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */
class Give_Admin {
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
	 * @return Give_Admin
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
		$this->admin_loading();
		$this->conditional_loading();
	}

	/**
	 *  Load core file
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function admin_loading() {
		require_once GIVE_PLUGIN_DIR . 'includes/admin/setting-page-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/give-metabox-functions.php'; // @todo: [refactor] can be load only for form edit screen. review possibilities
	}

	/**
	 *  Load file conditionally
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function conditional_loading() {
		if ( $this->is_generate_pdf() ) {
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/pdf-reports.php';
		}
	}

	/**
	 * Check if performing action 'generate_pdf'
	 *
	 * @since  2.4.0
	 * @access private
	 *
	 * @return bool
	 */
	private function is_generate_pdf() {
		return isset( $_GET['give-action'] ) && 'generate_pdf' === give_clean( $_GET['give-action'] );
	}
}

Give_Admin::get_instance();
