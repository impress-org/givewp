<?php
/**
 * This class will handle file loading for the wp-admin interface.
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */

/**
 * Class Give_Admin
 */
class Give_Admin {
	/**
	 * Instance.
	 *
	 * @since  2.4.0
	 * @access private
	 * @var
	 */
	private static $instance;

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
		require_once GIVE_PLUGIN_DIR . 'includes/admin/misc-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/setting-page-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/import-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/give-metabox-functions.php'; // @todo: [refactor] can be loaded only on the form edit screen. review possibilities

		require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-footer.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/class-give-welcome.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-pages.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/class-i18n-module.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-filters.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/add-ons/actions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/plugins.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/class-blank-slate.php';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/actions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/payments-history.php';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/donors/donors.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/donors/donor-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/donors/donor-actions.php';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/forms/metabox.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/forms/class-give-form-duplicator.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/forms/class-metabox-form-data.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/forms/dashboard-columns.php';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/export-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/export-actions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/give-export-donations-functions.php';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/reports/reports.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/reports/class-give-graph.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/reports/graphing.php';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/logs.php';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/tools-actions.php';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/abstract-shortcode-generator.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/class-shortcode-button.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-form.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-goal.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-login.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-register.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-profile-editor.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-donation-grid.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-donation-history.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-receipt.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-totals.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-donor-wall.php';
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
