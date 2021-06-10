<?php
/**
 * Plugin Name: Give - Donation Plugin
 * Plugin URI: https://givewp.com
 * Description: The most robust, flexible, and intuitive way to accept donations on WordPress.
 * Author: GiveWP
 * Author URI: https://givewp.com/
 * Version: 2.11.2
 * Text Domain: give
 * Domain Path: /languages
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
 * "Open source software is software that can be freely used, changed, and shared (in modified or unmodified form) by
 * anyone. Open source software is made by many people, and distributed under licenses that comply with the Open Source
 * Definition."
 *
 * -- The Open Source Initiative
 *
 * Give is a tribute to the spirit and philosophy of Open Source. We at GiveWP gladly embrace the Open Source
 * philosophy both in how Give itself was developed, and how we hope to see others build more from our code base.
 *
 * Give would not have been possible without the tireless efforts of WordPress and the surrounding Open Source projects
 * and their talented developers. Thank you all for your contribution to WordPress.
 *
 * - The GiveWP Team
 */

use Give\Container\Container;
use Give\Framework\Exceptions\UncaughtExceptionLogger;
use Give\Framework\Migrations\MigrationsServiceProvider;
use Give\Form\Templates;
use Give\Revenue\RevenueServiceProvider;
use Give\Route\Form as FormRoute;
use Give\ServiceProviders\PaymentGateways;
use Give\ServiceProviders\Routes;
use Give\ServiceProviders\LegacyServiceProvider;
use Give\ServiceProviders\RestAPI;
use Give\ServiceProviders\Onboarding;
use Give\MultiFormGoals\ServiceProvider as MultiFormGoalsServiceProvider;
use Give\DonorDashboards\ServiceProvider as DonorDashboardsServiceProvider;
use Give\Shims\ShimsServiceProvider;
use Give\TestData\ServiceProvider as TestDataServiceProvider;
use Give\MigrationLog\MigrationLogServiceProvider;
use Give\Log\LogServiceProvider;
use Give\ServiceProviders\ServiceProvider;
use Give\Form\LegacyConsumer\ServiceProvider as FormLegacyConsumerServiceProvider;
use Give\Tracking\TrackingServiceProvider;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Give Class
 *
 * @since 2.8.0 build in a service container
 * @since 1.0
 *
 * @property-read Give_API                        $api
 * @property-read Give_Async_Process              $async_process
 * @property-read Give_Comment                    $comment
 * @property-read Give_DB_Donors                  $donors
 * @property-read Give_DB_Donor_Meta              $donor_meta
 * @property-read Give_Emails                     $emails
 * @property-read Give_Email_Template_Tags        $email_tags
 * @property-read Give_DB_Form_Meta               $form_meta
 * @property-read Give_Admin_Settings             $give_settings
 * @property-read Give_HTML_Elements              $html
 * @property-read Give_Logging                    $logs
 * @property-read Give_Notices                    $notices
 * @property-read Give_DB_Payment_Meta            $payment_meta
 * @property-read Give_Roles                      $roles
 * @property-read FormRoute                       $routeForm
 * @property-read Templates                       $templates
 * @property-read Give_Scripts                    $scripts
 * @property-read Give_DB_Sequential_Ordering     $sequential_donation_db
 * @property-read Give_Sequential_Donation_Number $seq_donation_number
 * @property-read Give_Session                    $session
 * @property-read Give_DB_Sessions                $session_db
 * @property-read Give_Tooltips                   $tooltips
 *
 * @mixin Container
 */
final class Give {
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
	 * Give_Stripe Object.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var Give_Stripe
	 */
	public $stripe;

	/**
	 * @since 2.8.0
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * @since 2.8.0
	 *
	 * @var array Array of Service Providers to load
	 */
	private $serviceProviders = [
		LegacyServiceProvider::class,
		RestAPI::class,
		Routes::class,
		PaymentGateways::class,
		Onboarding::class,
		MigrationsServiceProvider::class,
		RevenueServiceProvider::class,
		MultiFormGoalsServiceProvider::class,
		DonorDashboardsServiceProvider::class,
		TrackingServiceProvider::class,
		TestDataServiceProvider::class,
		MigrationLogServiceProvider::class,
		LogServiceProvider::class,
		FormLegacyConsumerServiceProvider::class,
		ShimsServiceProvider::class,
	];

	/**
	 * @since 2.8.0
	 *
	 * @var bool Make sure the providers are loaded only once
	 */
	private $providersLoaded = false;

	/**
	 * Give constructor.
	 *
	 * Sets up the Container to be used for managing all other instances and data
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$this->container = new Container();
	}

	/**
	 * Bootstraps the Give Plugin
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		// PHP version
		if ( ! defined( 'GIVE_REQUIRED_PHP_VERSION' ) ) {
			define( 'GIVE_REQUIRED_PHP_VERSION', '5.6.0' );
		}

		// Bailout: Need minimum php version to load plugin.
		if ( function_exists( 'phpversion' ) && version_compare( GIVE_REQUIRED_PHP_VERSION, phpversion(), '>' ) ) {
			add_action( 'admin_notices', [ $this, 'minimum_phpversion_notice' ] );

			return;
		}

		$this->setup_constants();

		// Add compatibility notice for recurring and stripe support with Give 2.5.0.
		add_action( 'admin_notices', [ $this, 'display_old_recurring_compatibility_notice' ] );

		add_action( 'plugins_loaded', [ $this, 'init' ], 0 );

		register_activation_hook( GIVE_PLUGIN_FILE, [ $this, 'install' ] );

		do_action( 'give_loaded' );
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

		$this->bindClasses();

		$this->setupExceptionHandler();

		$this->loadServiceProviders();

		// Load form template
		$this->templates->load();

		// Load routes.
		$this->routeForm->init();

		/**
		 * Fire the action after Give core loads.
		 *
		 * @since 1.8.7
		 *
		 * @param Give class instance.
		 *
		 */
		do_action( 'give_init', $this );
	}

	/**
	 * Binds the initial classes to the service provider.
	 *
	 * @since 2.8.0
	 */
	private function bindClasses() {
		$this->container->singleton( 'templates', Templates::class );
		$this->container->singleton( 'routeForm', FormRoute::class );
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
		// Plugin version.
		if ( ! defined( 'GIVE_VERSION' ) ) {
			define( 'GIVE_VERSION', '2.11.2' );
		}

		// Plugin Root File.
		if ( ! defined( 'GIVE_PLUGIN_FILE' ) ) {
			define( 'GIVE_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Folder Path.
		if ( ! defined( 'GIVE_PLUGIN_DIR' ) ) {
			define( 'GIVE_PLUGIN_DIR', plugin_dir_path( GIVE_PLUGIN_FILE ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'GIVE_PLUGIN_URL' ) ) {
			define( 'GIVE_PLUGIN_URL', plugin_dir_url( GIVE_PLUGIN_FILE ) );
		}

		// Plugin Basename aka: "give/give.php".
		if ( ! defined( 'GIVE_PLUGIN_BASENAME' ) ) {
			define( 'GIVE_PLUGIN_BASENAME', plugin_basename( GIVE_PLUGIN_FILE ) );
		}

		// Make sure CAL_GREGORIAN is defined.
		if ( ! defined( 'CAL_GREGORIAN' ) ) {
			define( 'CAL_GREGORIAN', 1 );
		}
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

		$notice_desc  = '<p><strong>' . __(
			'Your site could be faster and more secure with a newer PHP version.',
			'give'
		) . '</strong></p>';
		$notice_desc .= '<p>' . __(
			'Hey, we\'ve noticed that you\'re running an outdated version of PHP. PHP is the programming language that WordPress and GiveWP are built on. The version that is currently used for your site is no longer supported. Newer versions of PHP are both faster and more secure. In fact, your version of PHP no longer receives security updates, which is why we\'re sending you this notice.',
			'give'
		) . '</p>';
		$notice_desc .= '<p>' . __(
			'Hosts have the ability to update your PHP version, but sometimes they don\'t dare to do that because they\'re afraid they\'ll break your site.',
			'give'
		) . '</p>';
		$notice_desc .= '<p><strong>' . __( 'To which version should I update?', 'give' ) . '</strong></p>';
		$notice_desc .= '<p>' . __(
			'You should update your PHP version to either 5.6 or to 7.0 or 7.1. On a normal WordPress site, switching to PHP 5.6 should never cause issues. We would however actually recommend you switch to PHP7. There are some plugins that are not ready for PHP7 though, so do some testing first. PHP7 is much faster than PHP 5.6. It\'s also the only PHP version still in active development and therefore the better option for your site in the long run.',
			'give'
		) . '</p>';
		$notice_desc .= '<p><strong>' . __( 'Can\'t update? Ask your host!', 'give' ) . '</strong></p>';
		$notice_desc .= '<p>' . sprintf(
			__(
				'If you cannot upgrade your PHP version yourself, you can send an email to your host. If they don\'t want to upgrade your PHP version, we would suggest you switch hosts. Have a look at one of the recommended %1$sWordPress hosting partners%2$s.',
				'give'
			),
			sprintf(
				'<a href="%1$s" target="_blank">',
				esc_url( 'https://wordpress.org/hosting/' )
			),
			'</a>'
		) . '</p>';

		echo sprintf(
			'<div class="notice notice-error">%1$s</div>',
			wp_kses_post( $notice_desc )
		);
	}

	/**
	 * Display compatibility notice for Give 2.5.0 and Recurring 1.8.13 when Stripe premium is not active.
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function display_old_recurring_compatibility_notice() {
		// Show notice, if incompatibility found.
		if (
			defined( 'GIVE_RECURRING_VERSION' )
			&& version_compare( GIVE_RECURRING_VERSION, '1.9.0', '<' )
			&& defined( 'GIVE_STRIPE_VERSION' )
			&& version_compare( GIVE_STRIPE_VERSION, '2.2.0', '<' )
		) {
			$message = sprintf(
				__(
					'<strong>Attention:</strong> GiveWP 2.5.0+ requires the latest version of the Recurring Donations add-on to process payments properly with Stripe. Please update to the latest version add-on to resolve compatibility issues. If your license is active, you should see the update available in WordPress. Otherwise, you can access the latest version by <a href="%1$s" target="_blank">logging into your account</a> and visiting <a href="%1$s" target="_blank">your downloads</a> page on the GiveWP website.',
					'give'
				),
				esc_url( 'https://givewp.com/wp-login.php' ),
				esc_url( 'https://givewp.com/my-account/#tab_downloads' )
			);

			Give()->notices->register_notice(
				[
					'id'               => 'give-compatibility-with-old-recurring',
					'description'      => $message,
					'dismissible_type' => 'user',
					'dismiss_interval' => 'shortly',
				]
			);
		}
	}

	public function install() {
		$this->loadServiceProviders();
		give_install();
	}

	/**
	 * Load all the service providers to bootstrap the various parts of the application.
	 *
	 * @since 2.8.0
	 */
	private function loadServiceProviders() {
		if ( $this->providersLoaded ) {
			return;
		}

		$providers = [];

		foreach ( $this->serviceProviders as $serviceProvider ) {
			if ( ! is_subclass_of( $serviceProvider, ServiceProvider::class ) ) {
				throw new InvalidArgumentException( "$serviceProvider class must implement the ServiceProvider interface" );
			}

			/** @var ServiceProvider $serviceProvider */
			$serviceProvider = new $serviceProvider();

			$serviceProvider->register();

			$providers[] = $serviceProvider;
		}

		foreach ( $providers as $serviceProvider ) {
			$serviceProvider->boot();
		}

		$this->providersLoaded = true;
	}

	/**
	 * Register a Service Provider for bootstrapping
	 *
	 * @since 2.8.0
	 *
	 * @param string $serviceProvider
	 */
	public function registerServiceProvider( $serviceProvider ) {
		$this->serviceProviders[] = $serviceProvider;
	}

	/**
	 * Magic properties are passed to the service container to retrieve the data.
	 *
	 * @since 2.8.0 retrieve from the service container
	 * @since 2.7.0
	 *
	 * @param string $propertyName
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function __get( $propertyName ) {
		return $this->container->get( $propertyName );
	}

	/**
	 * Magic methods are passed to the service container.
	 *
	 * @since 2.8.0
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		return call_user_func_array( [ $this->container, $name ], $arguments );
	}

	/**
	 * Sets up the Exception Handler to catch and handle uncaught exceptions
	 *
	 * @unreleased
	 */
	private function setupExceptionHandler() {
		$handler = new UncaughtExceptionLogger();
		$handler->setupExceptionHandler();
	}
}

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
 * @since 2.8.0 add parameter for quick retrieval from container
 * @since 1.0
 *
 * @param null $abstract Selector for data to retrieve from the service container
 *
 * @return object|Give
 */
function give( $abstract = null ) {
	static $instance = null;

	if ( $instance === null ) {
		$instance = new Give();
	}

	if ( $abstract !== null ) {
		return $instance->make( $abstract );
	}

	return $instance;
}

require __DIR__ . '/vendor/autoload.php';

give()->boot();
