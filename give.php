<?php

/**
 * Plugin Name: Give - Donation Plugin
 * Plugin URI: https://givewp.com
 * Description: The most robust, flexible, and intuitive way to accept donations on WordPress.
 * Author: GiveWP
 * Author URI: https://givewp.com/
 * Version: 2.27.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
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
use Give\DonationForms\Repositories\DonationFormsRepository;
use Give\DonationForms\ServiceProvider as DonationFormsServiceProvider;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ServiceProvider as DonationServiceProvider;
use Give\DonationSummary\ServiceProvider as DonationSummaryServiceProvider;
use Give\DonorDashboards\Profile;
use Give\DonorDashboards\ServiceProvider as DonorDashboardsServiceProvider;
use Give\DonorDashboards\Tabs\TabsRegister;
use Give\Donors\Repositories\DonorRepositoryProxy;
use Give\Donors\ServiceProvider as DonorsServiceProvider;
use Give\Form\LegacyConsumer\ServiceProvider as FormLegacyConsumerServiceProvider;
use Give\Form\Templates;
use Give\Framework\Database\ServiceProvider as DatabaseServiceProvider;
use Give\Framework\DesignSystem\DesignSystemServiceProvider;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Exceptions\UncaughtExceptionLogger;
use Give\Framework\Http\ServiceProvider as HttpServiceProvider;
use Give\Framework\Migrations\MigrationsServiceProvider;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\ValidationRules\ValidationRulesServiceProvider;
use Give\Framework\WordPressShims\ServiceProvider as WordPressShimsServiceProvider;
use Give\LegacySubscriptions\ServiceProvider as LegacySubscriptionsServiceProvider;
use Give\License\LicenseServiceProvider;
use Give\Log\LogServiceProvider;
use Give\MigrationLog\MigrationLogServiceProvider;
use Give\MultiFormGoals\ServiceProvider as MultiFormGoalsServiceProvider;
use Give\PaymentGateways\ServiceProvider as PaymentGatewaysServiceProvider;
use Give\Promotions\ServiceProvider as PromotionsServiceProvider;
use Give\Revenue\RevenueServiceProvider;
use Give\Route\Form as FormRoute;
use Give\ServiceProviders\GlobalStyles as GlobalStylesServiceProvider;
use Give\ServiceProviders\LegacyServiceProvider;
use Give\ServiceProviders\Onboarding;
use Give\ServiceProviders\PaymentGateways;
use Give\ServiceProviders\RestAPI;
use Give\ServiceProviders\Routes;
use Give\ServiceProviders\ServiceProvider;
use Give\Subscriptions\Repositories\SubscriptionRepository;
use Give\Subscriptions\ServiceProvider as SubscriptionServiceProvider;
use Give\TestData\ServiceProvider as TestDataServiceProvider;
use Give\Tracking\TrackingServiceProvider;
use Give\VendorOverrides\Validation\ValidationServiceProvider;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Give Class
 *
 * @since 2.21.0 Remove php dependency validation logic and constant
 * @since 2.19.6 add $donations, $subscriptions, and replace $donors class with DonorRepositoryProxy
 * @since 2.8.0 build in a service container
 * @since 1.0
 *
 * @property-read Give_API $api
 * @property-read Give_Async_Process $async_process
 * @property-read Give_Comment $comment
 * @property-read Give_DB_Donor_Meta $donor_meta
 * @property-read Give_Emails $emails
 * @property-read Give_Email_Template_Tags $email_tags
 * @property-read Give_DB_Form_Meta $form_meta
 * @property-read Give_Admin_Settings $give_settings
 * @property-read Give_HTML_Elements $html
 * @property-read Give_Logging $logs
 * @property-read Give_Notices $notices
 * @property-read Give_DB_Payment_Meta $payment_meta
 * @property-read Give_Roles $roles
 * @property-read FormRoute $routeForm
 * @property-read Templates $templates
 * @property-read Give_Scripts $scripts
 * @property-read Give_DB_Sequential_Ordering $sequential_donation_db
 * @property-read Give_Sequential_Donation_Number $seq_donation_number
 * @property-read Give_Session $session
 * @property-read Give_DB_Sessions $session_db
 * @property-read Give_Tooltips $tooltips
 * @property-read PaymentGatewayRegister $gateways
 * @property-read DonationRepository $donations
 * @property-read DonorRepositoryProxy $donors
 * @property-read SubscriptionRepository $subscriptions
 * @property-read DonationFormsRepository $donationForms
 * @property-read Profile $donorDashboard
 * @property-read TabsRegister $donorDashboardTabs
 * @property-read Give_Recurring_DB_Subscription_Meta $subscription_meta
 *
 * @mixin Container
 */
final class Give
{
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
     * @since 2.25.0 added HttpServiceProvider
     * @since      2.19.6 added Donors, Donations, and Subscriptions
     * @since      2.8.0
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
        LicenseServiceProvider::class,
        Give\Email\ServiceProvider::class,
        DonationSummaryServiceProvider::class,
        PaymentGatewaysServiceProvider::class,
        LegacySubscriptionsServiceProvider::class,
        Give\Exports\ServiceProvider::class,
        DonationServiceProvider::class,
        DonorsServiceProvider::class,
        SubscriptionServiceProvider::class,
        DonationFormsServiceProvider::class,
        PromotionsServiceProvider::class,
        LegacySubscriptionsServiceProvider::class,
        WordPressShimsServiceProvider::class,
        DatabaseServiceProvider::class,
        GlobalStylesServiceProvider::class,
        ValidationServiceProvider::class,
        ValidationRulesServiceProvider::class,
        HttpServiceProvider::class,
        DesignSystemServiceProvider::class,
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
    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * Bootstraps the Give Plugin
     *
     * @since 2.8.0
     */
    public function boot()
    {
        $this->setup_constants();

        // Add compatibility notice for recurring and stripe support with Give 2.5.0.
        add_action('admin_notices', [$this, 'display_old_recurring_compatibility_notice']);

        add_action('plugins_loaded', [$this, 'init'], 0);

        register_activation_hook(GIVE_PLUGIN_FILE, [$this, 'install']);

        do_action('give_loaded');
    }

    /**
     * Init Give when WordPress Initializes.
     *
     * @since 1.8.9
     */
    public function init()
    {
        /**
         * Fires before the Give core is initialized.
         *
         * @since 1.8.9
         */
        do_action('before_give_init');

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
        do_action('give_init', $this);
    }

    /**
     * Binds the initial classes to the service provider.
     *
     * @since 2.8.0
     */
    private function bindClasses()
    {
        $this->container->singleton('templates', Templates::class);
        $this->container->singleton('routeForm', FormRoute::class);
    }

    /**
     * Setup plugin constants
     *
     * @since  1.0
     * @access private
     *
     * @return void
     */
    private function setup_constants()
    {
        // Plugin version.
        if (!defined('GIVE_VERSION')) {
            define('GIVE_VERSION', '2.27.0');
        }

        // Plugin Root File.
        if (!defined('GIVE_PLUGIN_FILE')) {
            define('GIVE_PLUGIN_FILE', __FILE__);
        }

        // Plugin Folder Path.
        if (!defined('GIVE_PLUGIN_DIR')) {
            define('GIVE_PLUGIN_DIR', plugin_dir_path(GIVE_PLUGIN_FILE));
        }

        // Plugin Folder URL.
        if (!defined('GIVE_PLUGIN_URL')) {
            define('GIVE_PLUGIN_URL', plugin_dir_url(GIVE_PLUGIN_FILE));
        }

        // Plugin Basename aka: "give/give.php".
        if (!defined('GIVE_PLUGIN_BASENAME')) {
            define('GIVE_PLUGIN_BASENAME', plugin_basename(GIVE_PLUGIN_FILE));
        }

        // Make sure CAL_GREGORIAN is defined.
        if (!defined('CAL_GREGORIAN')) {
            define('CAL_GREGORIAN', 1);
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
    public function load_textdomain()
    {
        // Set filter for Give's languages directory
        $give_lang_dir = dirname(plugin_basename(GIVE_PLUGIN_FILE)) . '/languages/';
        $give_lang_dir = apply_filters('give_languages_directory', $give_lang_dir);

        // Traditional WordPress plugin locale filter.
        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
        $locale = apply_filters('plugin_locale', $locale, 'give');

        unload_textdomain('give');
        load_textdomain('give', WP_LANG_DIR . '/give/give-' . $locale . '.mo');
        load_plugin_textdomain('give', false, $give_lang_dir);
    }

    /**
     * Display compatibility notice for Give 2.5.0 and Recurring 1.8.13 when Stripe premium is not active.
     *
     * @since 2.5.0
     *
     * @return void
     */
    public function display_old_recurring_compatibility_notice()
    {
        // Show notice, if incompatibility found.
        if (
            defined('GIVE_RECURRING_VERSION')
            && version_compare(GIVE_RECURRING_VERSION, '1.9.0', '<')
            && defined('GIVE_STRIPE_VERSION')
            && version_compare(GIVE_STRIPE_VERSION, '2.2.0', '<')
        ) {
            $message = sprintf(
                __(
                    '<strong>Attention:</strong> GiveWP 2.5.0+ requires the latest version of the Recurring Donations add-on to process payments properly with Stripe. Please update to the latest version add-on to resolve compatibility issues. If your license is active, you should see the update available in WordPress. Otherwise, you can access the latest version by <a href="%1$s" target="_blank">logging into your account</a> and visiting <a href="%1$s" target="_blank">your downloads</a> page on the GiveWP website.',
                    'give'
                ),
                esc_url('https://givewp.com/wp-login.php'),
                esc_url('https://givewp.com/my-account/#tab_downloads')
            );

            Give()->notices->register_notice(
                [
                    'id' => 'give-compatibility-with-old-recurring',
                    'description' => $message,
                    'dismissible_type' => 'user',
                    'dismiss_interval' => 'shortly',
                ]
            );
        }
    }

    public function install()
    {
        $this->loadServiceProviders();
        give_install();
    }

    /**
     * Load all the service providers to bootstrap the various parts of the application.
     *
     * @since 2.8.0
     */
    private function loadServiceProviders()
    {
        if ($this->providersLoaded) {
            return;
        }

        $providers = [];

        foreach ($this->serviceProviders as $serviceProvider) {
            if (!is_subclass_of($serviceProvider, ServiceProvider::class)) {
                throw new InvalidArgumentException(
                    "$serviceProvider class must implement the ServiceProvider interface"
                );
            }

            /** @var ServiceProvider $serviceProvider */
            $serviceProvider = new $serviceProvider();

            $serviceProvider->register();

            $providers[] = $serviceProvider;
        }

        foreach ($providers as $serviceProvider) {
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
    public function registerServiceProvider($serviceProvider)
    {
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
    public function __get($propertyName)
    {
        return $this->container->get($propertyName);
    }

    /**
     * Magic methods are passed to the service container.
     *
     * @since 2.8.0
     *
     * @param $arguments
     *
     * @param $name
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container, $name], $arguments);
    }

    /**
     * Retrieves the underlying container instance. This isn't usually necessary, but sometimes we want to pass along
     * the container itself.
     *
     * @since 2.24.0
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Sets up the Exception Handler to catch and handle uncaught exceptions
     *
     * @since 2.11.1
     */
    private function setupExceptionHandler()
    {
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
function give($abstract = null)
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Give();
    }

    if ($abstract !== null) {
        return $instance->make($abstract);
    }

    return $instance;
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/vendor-prefixed/autoload.php';

give()->boot();
