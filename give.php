<?php
/**
 * Plugin Name: Give - Donation Plugin
 * Plugin URI: https://givewp.com
 * Description: The most robust, flexible, and intuitive way to accept donations on WordPress.
 * Author: WordImpress
 * Author URI: https://wordimpress.com
 * Version: 2.1.0
 * Text Domain: give
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WordImpress/Give
 *
 * Give is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Give is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Give. If not, see <https://www.gnu.org/licenses/>.
 *
 * A Tribute to Open Source:
 *
 * "Open source software is software that can be freely used, changed, and shared (in modified or unmodified form) by anyone. Open
 * source software is made by many people, and distributed under licenses that comply with the Open Source Definition."
 *
 * -- The Open Source Initiative
 *
 * Give is a tribute to the spirit and philosophy of Open Source. We at WordImpress gladly embrace the Open Source philosophy both
 * in how Give itself was developed, and how we hope to see others build more from our code base.
 *
 * Give would not have been possible without the tireless efforts of WordPress and the surrounding Open Source projects and their talented developers. Thank you all for your contribution to WordPress.
 *
 * - The WordImpress Team
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give' ) ) :

	/**
	 * Main Give Class
	 *
	 * @since 1.0
	 */
	final class Give {

		/** Singleton *************************************************************/

		/**
		 * Give Instance
		 *
		 * @since  1.0
		 * @access private
		 *
		 * @var    Give() The one true Give
		 */
		protected static $_instance;

		/**
		 * Give Roles Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_Roles object
		 */
		public $roles;

		/**
		 * Give Settings Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_Admin_Settings object
		 */
		public $give_settings;

		/**
		 * Give Session Object
		 *
		 * This holds donation data for user's session.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_Session object
		 */
		public $session;

		/**
		 * Give HTML Element Helper Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_HTML_Elements object
		 */
		public $html;

		/**
		 * Give Emails Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_Emails object
		 */
		public $emails;

		/**
		 * Give Email Template Tags Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_Email_Template_Tags object
		 */
		public $email_tags;

		/**
		 * Give Donors DB Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_DB_Donors object
		 */
		public $donors;

		/**
		 * Give Donor meta DB Object
		 *
		 * @since  1.6
		 * @access public
		 *
		 * @var    Give_DB_Donor_Meta object
		 */
		public $donor_meta;

		/**
		 * Give Sequential Donation DB Object
		 *
		 * @since  2.1.0
		 * @access public
		 *
		 * @var    Give_DB_Sequential_Ordering object
		 */
		public $sequential_donation_db;

		/**
		 * Give API Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_API object
		 */
		public $api;

		/**
		 * Give Template Loader Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_Template_Loader object
		 */
		public $template_loader;

		/**
		 * Give No Login Object
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @var    Give_Email_Access object
		 */
		public $email_access;

		/**
		 * Give_tooltips Object
		 *
		 * @since  1.8.9
		 * @access public
		 *
		 * @var    Give_Tooltips object
		 */
		public $tooltips;

		/**
		 * Give notices Object
		 *
		 * @var    Give_Notices $notices
		 */
		public $notices;


		/**
		 * Give logging Object
		 *
		 * @var    Give_Logging $logs
		 */
		public $logs;

		/**
		 * Give payment Object
		 *
		 * @var    Give_DB_Payment_Meta $payment_meta
		 */
		public $payment_meta;

		/**
		 * Give form Object
		 *
		 * @var    Give_DB_Form_Meta $form_meta
		 */
		public $form_meta;

		/**
		 * Give form Object
		 *
		 * @var    Give_Async_Process $async_process
		 */
		public $async_process;

		/**
		 * Give scripts Object.
		 *
		 * @var Give_Scripts
		 */
		public $scripts;

		/**
		 * Give_Seq_Donation_Number Object.
		 *
		 * @var Give_Sequential_Donation_Number
		 */
		public $seq_donation_number;

		/**
		 * Main Give Instance
		 *
		 * Ensures that only one instance of Give exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since     1.0
		 * @access    public
		 *
		 * @static
		 * @see       Give()
		 *
		 * @return    Give
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Give Constructor.
		 */
		public function __construct() {
			// PHP version
			if ( ! defined( 'GIVE_REQUIRED_PHP_VERSION' ) ) {
				define( 'GIVE_REQUIRED_PHP_VERSION', '5.3' );
			}

			// Bailout: Need minimum php version to load plugin.
			if ( function_exists( 'phpversion' ) && version_compare( GIVE_REQUIRED_PHP_VERSION, phpversion(), '>' ) ) {
				add_action( 'admin_notices', array( $this, 'minimum_phpversion_notice' ) );

				return;
			}

			$this->setup_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'give_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since  1.8.9
		 */
		private function init_hooks() {
			register_activation_hook( GIVE_PLUGIN_FILE, 'give_install' );
			add_action( 'plugins_loaded', array( $this, 'init' ), 0 );

			// Set up localization on init Hook.
			add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		}


		/**
		 * Init Give when WordPress Initializes.
		 *
		 * @since 1.8.9
		 */
		public function init() {

			/**
			 * Fires before the Give core is initialized.
			 *
			 * @since 1.8.9
			 */
			do_action( 'before_give_init' );

			// Set up localization.
			$this->load_textdomain();

			$this->roles                  = new Give_Roles();
			$this->api                    = new Give_API();
			$this->give_settings          = new Give_Admin_Settings();
			$this->session                = new Give_Session();
			$this->html                   = new Give_HTML_Elements();
			$this->emails                 = new Give_Emails();
			$this->email_tags             = new Give_Email_Template_Tags();
			$this->donors                 = new Give_DB_Donors();
			$this->donor_meta             = new Give_DB_Donor_Meta();
			$this->template_loader        = new Give_Template_Loader();
			$this->email_access           = new Give_Email_Access();
			$this->tooltips               = new Give_Tooltips();
			$this->notices                = new Give_Notices();
			$this->payment_meta           = new Give_DB_Payment_Meta();
			$this->logs                   = new Give_Logging();
			$this->form_meta              = new Give_DB_Form_Meta();
			$this->sequential_donation_db = new Give_DB_Sequential_Ordering();
			$this->async_process          = new Give_Async_Process();
			$this->scripts                = new Give_Scripts();
			$this->seq_donation_number    = Give_Sequential_Donation_Number::get_instance();

			/**
			 * Fire the action after Give core loads.
			 *
			 * @param Give class instance.
			 *
			 * @since 1.8.7
			 */
			do_action( 'give_init', $this );

		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object, therefore we don't want the object to be cloned.
		 *
		 * @since  1.0
		 * @access protected
		 *
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			give_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'give' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since  1.0
		 * @access protected
		 *
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			give_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'give' ), '1.0' );
		}

		/**
		 * Setup plugin constants
		 *
		 * @since  1.0
		 * @access private
		 *
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version
			if ( ! defined( 'GIVE_VERSION' ) ) {
				define( 'GIVE_VERSION', '2.1.0' );
			}

			// Plugin Root File
			if ( ! defined( 'GIVE_PLUGIN_FILE' ) ) {
				define( 'GIVE_PLUGIN_FILE', __FILE__ );
			}

			// Plugin Folder Path
			if ( ! defined( 'GIVE_PLUGIN_DIR' ) ) {
				define( 'GIVE_PLUGIN_DIR', plugin_dir_path( GIVE_PLUGIN_FILE ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'GIVE_PLUGIN_URL' ) ) {
				define( 'GIVE_PLUGIN_URL', plugin_dir_url( GIVE_PLUGIN_FILE ) );
			}

			// Plugin Basename aka: "give/give.php"
			if ( ! defined( 'GIVE_PLUGIN_BASENAME' ) ) {
				define( 'GIVE_PLUGIN_BASENAME', plugin_basename( GIVE_PLUGIN_FILE ) );
			}

			// Make sure CAL_GREGORIAN is defined
			if ( ! defined( 'CAL_GREGORIAN' ) ) {
				define( 'CAL_GREGORIAN', 1 );
			}
		}

		/**
		 * Include required files
		 *
		 * @since  1.0
		 * @access private
		 *
		 * @return void
		 */
		private function includes() {
			global $give_options;

			/**
			 * Load libraries.
			 */
			if ( ! class_exists( 'WP_Async_Request' ) ) {
				include_once( GIVE_PLUGIN_DIR . 'includes/libraries/wp-async-request.php' );
			}

			if ( ! class_exists( 'WP_Background_Process' ) ) {
				include_once( GIVE_PLUGIN_DIR . 'includes/libraries/wp-background-process.php' );
			}

			/**
			 * Load plugin files
			 */
			require_once GIVE_PLUGIN_DIR . 'includes/admin/class-admin-settings.php';
			require_once GIVE_PLUGIN_DIR . 'includes/admin/class-give-settings.php';
			$give_options = give_get_settings();

			require_once GIVE_PLUGIN_DIR . 'includes/class-give-cron.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-async-process.php';
			require_once GIVE_PLUGIN_DIR . 'includes/admin/give-metabox-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-cache.php';
			require_once GIVE_PLUGIN_DIR . 'includes/post-types.php';
			require_once GIVE_PLUGIN_DIR . 'includes/ajax-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/filters.php';
			require_once GIVE_PLUGIN_DIR . 'includes/api/class-give-api.php';
			require_once GIVE_PLUGIN_DIR . 'includes/api/class-give-api-v2.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-tooltips.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-notices.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-translation.php';

			require_once GIVE_PLUGIN_DIR . 'includes/class-give-scripts.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-roles.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-template-loader.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-donate-form.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-db.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-db-meta.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-db-donors.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-db-donor-meta.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-donor.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-stats.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-session.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-html-elements.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-logging.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-license-handler.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-email-access.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-db-payment-meta.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-db-form-meta.php';
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-db-sequential-ordering.php';

			require_once GIVE_PLUGIN_DIR . 'includes/country-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/template-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/misc-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/import-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/forms/functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/forms/template.php';
			require_once GIVE_PLUGIN_DIR . 'includes/forms/widget.php';
			require_once GIVE_PLUGIN_DIR . 'includes/shortcodes.php';
			require_once GIVE_PLUGIN_DIR . 'includes/formatting.php';
			require_once GIVE_PLUGIN_DIR . 'includes/currency-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/price-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/error-tracking.php';
			require_once GIVE_PLUGIN_DIR . 'includes/process-donation.php';
			require_once GIVE_PLUGIN_DIR . 'includes/login-register.php';
			require_once GIVE_PLUGIN_DIR . 'includes/user-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/plugin-compatibility.php';
			require_once GIVE_PLUGIN_DIR . 'includes/deprecated/deprecated-classes.php';
			require_once GIVE_PLUGIN_DIR . 'includes/deprecated/deprecated-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/deprecated/deprecated-actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/deprecated/deprecated-filters.php';

			require_once GIVE_PLUGIN_DIR . 'includes/payments/backward-compatibility.php';
			require_once GIVE_PLUGIN_DIR . 'includes/payments/functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/payments/actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
			require_once GIVE_PLUGIN_DIR . 'includes/payments/class-payments-query.php';
			require_once GIVE_PLUGIN_DIR . 'includes/payments/class-give-payment.php';
			require_once GIVE_PLUGIN_DIR . 'includes/payments/class-give-sequential-donation-number.php';

			require_once GIVE_PLUGIN_DIR . 'includes/gateways/functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/paypal-standard.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/offline-donations.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/manual.php';

			require_once GIVE_PLUGIN_DIR . 'includes/emails/class-give-emails.php';
			require_once GIVE_PLUGIN_DIR . 'includes/emails/class-give-email-tags.php';
			require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/class-email-notifications.php';
			require_once GIVE_PLUGIN_DIR . 'includes/emails/functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/emails/template.php';
			require_once GIVE_PLUGIN_DIR . 'includes/emails/actions.php';

			require_once GIVE_PLUGIN_DIR . 'includes/donors/class-give-donors-query.php';
			require_once GIVE_PLUGIN_DIR . 'includes/donors/backward-compatibility.php';

			require_once GIVE_PLUGIN_DIR . 'includes/admin/upgrades/class-give-updates.php';

			require_once GIVE_PLUGIN_DIR . 'blocks/load.php';

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once GIVE_PLUGIN_DIR . 'includes/class-give-cli-commands.php';
			}

			if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

				require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-footer.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/welcome.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-pages.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/class-api-keys-table.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/class-i18n-module.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-filters.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/add-ons.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/plugins.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/class-blank-slate.php';

				require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/actions.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/payments/payments-history.php';

				require_once GIVE_PLUGIN_DIR . 'includes/admin/donors/donors.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/donors/donor-functions.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/donors/donor-actions.php';

				require_once GIVE_PLUGIN_DIR . 'includes/admin/forms/metabox.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/forms/class-metabox-form-data.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/forms/dashboard-columns.php';

				require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/export-functions.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/export-actions.php';
				require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/pdf-reports.php';
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
			}// End if().

			require_once GIVE_PLUGIN_DIR . 'includes/install.php';

		}

		/**
		 * Loads the plugin language files.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return void
		 */
		public function load_textdomain() {

			// Set filter for Give's languages directory
			$give_lang_dir = dirname( plugin_basename( GIVE_PLUGIN_FILE ) ) . '/languages/';
			$give_lang_dir = apply_filters( 'give_languages_directory', $give_lang_dir );

			// Traditional WordPress plugin locale filter.
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'give' );

			unload_textdomain( 'give' );
			load_textdomain( 'give', WP_LANG_DIR . '/give/give-' . $locale . '.mo' );
			load_plugin_textdomain( 'give', false, $give_lang_dir );

		}


		/**
		 *  Show minimum PHP version notice.
		 *
		 * @since  1.8.12
		 * @access public
		 */
		public function minimum_phpversion_notice() {
			// Bailout.
			if ( ! is_admin() ) {
				return;
			}

			$notice_desc  = '<p><strong>' . __( 'Your site could be faster and more secure with a newer PHP version.', 'give' ) . '</strong></p>';
			$notice_desc .= '<p>' . __( 'Hey, we\'ve noticed that you\'re running an outdated version of PHP. PHP is the programming language that WordPress and Give are built on. The version that is currently used for your site is no longer supported. Newer versions of PHP are both faster and more secure. In fact, your version of PHP no longer receives security updates, which is why we\'re sending you this notice.', 'give' ) . '</p>';
			$notice_desc .= '<p>' . __( 'Hosts have the ability to update your PHP version, but sometimes they don\'t dare to do that because they\'re afraid they\'ll break your site.', 'give' ) . '</p>';
			$notice_desc .= '<p><strong>' . __( 'To which version should I update?', 'give' ) . '</strong></p>';
			$notice_desc .= '<p>' . __( 'You should update your PHP version to either 5.6 or to 7.0 or 7.1. On a normal WordPress site, switching to PHP 5.6 should never cause issues. We would however actually recommend you switch to PHP7. There are some plugins that are not ready for PHP7 though, so do some testing first. PHP7 is much faster than PHP 5.6. It\'s also the only PHP version still in active development and therefore the better option for your site in the long run.', 'give' ) . '</p>';
			$notice_desc .= '<p><strong>' . __( 'Can\'t update? Ask your host!', 'give' ) . '</strong></p>';
			$notice_desc .= '<p>' . sprintf( __( 'If you cannot upgrade your PHP version yourself, you can send an email to your host. If they don\'t want to upgrade your PHP version, we would suggest you switch hosts. Have a look at one of the recommended %1$sWordPress hosting partners%2$s.', 'give' ), sprintf( '<a href="%1$s" target="_blank">', esc_url( 'https://wordpress.org/hosting/' ) ), '</a>' ) . '</p>';

			echo sprintf(
				'<div class="notice notice-error">%1$s</div>',
				wp_kses_post( $notice_desc )
			);
		}

	}

endif; // End if class_exists check


/**
 * Start Give
 *
 * The main function responsible for returning the one true Give instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $give = Give(); ?>
 *
 * @since 1.0
 * @return object|Give
 */
function Give() {
	return Give::instance();
}

Give();
