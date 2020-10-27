<?php

namespace Give\ServiceProviders;

use Closure;
use Give\Route\Form;

/**
 * Class LegacyServiceProvider
 *
 * This handles the loading of all of the legacy codebase included in the /includes directory.
 * DO NOT EXTEND THIS WITH NEW CODE as it is intended to shrink over time as we migrate over
 * to the new ways of doing things.
 */
class LegacyServiceProvider implements ServiceProvider {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->includeLegacyFiles();
		$this->bindClasses();
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
	}

	/**
	 * Load all the legacy class files since they don't have auto-loading
	 *
	 * @since 2.8.0
	 */
	private function includeLegacyFiles() {
		global $give_options;

		require_once GIVE_PLUGIN_DIR . 'includes/class-give-cache-setting.php';

		/**
		 * Load libraries.
		 */
		if ( ! class_exists( 'WP_Async_Request' ) ) {
			include_once GIVE_PLUGIN_DIR . 'includes/libraries/wp-async-request.php';
		}

		if ( ! class_exists( 'WP_Background_Process' ) ) {
			include_once GIVE_PLUGIN_DIR . 'includes/libraries/wp-background-process.php';
		}

		require_once GIVE_PLUGIN_DIR . 'includes/setting-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/country-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/template-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/forms/functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/ajax-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/currency-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/price-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/user-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/donors/frontend-donor-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/payments/functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/gateways/functions.php';

		/**
		 * Load plugin files
		 */
		require_once GIVE_PLUGIN_DIR . 'includes/admin/class-admin-settings.php';
		$give_options = give_get_settings();

		require_once GIVE_PLUGIN_DIR . 'includes/class-give-cron.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-async-process.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-cache.php';
		require_once GIVE_PLUGIN_DIR . 'includes/post-types.php';
		require_once GIVE_PLUGIN_DIR . 'includes/filters.php';
		require_once GIVE_PLUGIN_DIR . 'includes/api/class-give-api-v2.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-tooltips.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-notices.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-translation.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-license-handler.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/class-give-html-elements.php';

		require_once GIVE_PLUGIN_DIR . 'includes/class-give-scripts.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-roles.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-donate-form.php';

		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-meta.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-comments.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-comments-meta.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-donors.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-donor-meta.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-form-meta.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-sequential-ordering.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-logs.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-logs-meta.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-sessions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/database/class-give-db-payment-meta.php';

		require_once GIVE_PLUGIN_DIR . 'includes/class-give-donor.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-stats.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-session.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-logging.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-comment.php';

		require_once GIVE_PLUGIN_DIR . 'includes/forms/widget.php';
		require_once GIVE_PLUGIN_DIR . 'includes/forms/class-give-forms-query.php';
		require_once GIVE_PLUGIN_DIR . 'includes/forms/template.php';
		require_once GIVE_PLUGIN_DIR . 'includes/shortcodes.php';
		require_once GIVE_PLUGIN_DIR . 'includes/formatting.php';
		require_once GIVE_PLUGIN_DIR . 'includes/error-tracking.php';
		require_once GIVE_PLUGIN_DIR . 'includes/login-register.php';
		require_once GIVE_PLUGIN_DIR . 'includes/plugin-compatibility.php';
		require_once GIVE_PLUGIN_DIR . 'includes/deprecated/deprecated-classes.php';
		require_once GIVE_PLUGIN_DIR . 'includes/deprecated/deprecated-functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/deprecated/deprecated-actions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/deprecated/deprecated-filters.php';

		require_once GIVE_PLUGIN_DIR . 'includes/process-donation.php';
		require_once GIVE_PLUGIN_DIR . 'includes/payments/backward-compatibility.php';
		require_once GIVE_PLUGIN_DIR . 'includes/payments/actions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/payments/class-payment-stats.php';
		require_once GIVE_PLUGIN_DIR . 'includes/payments/class-payments-query.php';
		require_once GIVE_PLUGIN_DIR . 'includes/payments/class-give-payment.php';
		require_once GIVE_PLUGIN_DIR . 'includes/payments/class-give-sequential-donation-number.php';

		require_once GIVE_PLUGIN_DIR . 'includes/gateways/actions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/gateways/paypal/paypal-standard.php';
		require_once GIVE_PLUGIN_DIR . 'includes/gateways/offline-donations.php';
		require_once GIVE_PLUGIN_DIR . 'includes/gateways/manual.php';
		require_once GIVE_PLUGIN_DIR . 'includes/emails/class-give-emails.php';
		require_once GIVE_PLUGIN_DIR . 'includes/emails/class-give-email-tags.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/class-email-notifications.php';
		require_once GIVE_PLUGIN_DIR . 'includes/emails/functions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/emails/template.php';
		require_once GIVE_PLUGIN_DIR . 'includes/emails/actions.php';

		require_once GIVE_PLUGIN_DIR . 'includes/donors/class-give-donors-query.php';
		require_once GIVE_PLUGIN_DIR . 'includes/donors/class-give-donor-wall.php';
		require_once GIVE_PLUGIN_DIR . 'includes/donors/class-give-donor-stats.php';
		require_once GIVE_PLUGIN_DIR . 'includes/donors/backward-compatibility.php';
		require_once GIVE_PLUGIN_DIR . 'includes/donors/actions.php';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/upgrades/class-give-updates.php';

		require_once GIVE_PLUGIN_DIR . 'blocks/load.php';

		// Include Views
		require_once GIVE_PLUGIN_DIR . 'src/Views/Views.php';

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once GIVE_PLUGIN_DIR . 'includes/class-give-cli-commands.php';
		}

		// Load file for frontend
		if ( $this->is_request( 'frontend' ) ) {
			require_once GIVE_PLUGIN_DIR . 'includes/frontend/class-give-frontend.php';
		}

		if ( $this->is_request( 'admin' ) || $this->is_request( 'wpcli' ) ) {
			require_once GIVE_PLUGIN_DIR . 'includes/admin/class-give-admin.php';
		}// End if().

		require_once GIVE_PLUGIN_DIR . 'includes/actions.php';
		require_once GIVE_PLUGIN_DIR . 'includes/install.php';

		// This conditional check will add backward compatibility to older Stripe versions (i.e. < 2.2.0) when used with Give 2.5.0.
		if (
			! defined( 'GIVE_STRIPE_VERSION' ) ||
			(
				defined( 'GIVE_STRIPE_VERSION' ) &&
				version_compare( GIVE_STRIPE_VERSION, '2.2.0', '>=' )
			)
		) {
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/class-give-stripe.php';
		}
	}

	/**
	 * Binds the legacy classes to the service provider
	 *
	 * @since 2.8.0
	 */
	private function bindClasses() {
		give()->singleton( 'routeForm', Form::class );

		$this->bindInstance( 'roles', 'Give_Roles', 'class-give-roles.php' );
		$this->bindInstance( 'give_settings', 'Give_Admin_Settings', 'admin/class-admin-settings.php' );
		$this->bindInstance( 'api', 'Give_API', 'api/class-give-api.php' );
		$this->bindInstance( 'emails', 'Give_Emails', 'emails/class-give-emails.php' );
		$this->bindInstance( 'email_tags', 'Give_Email_Template_Tags', 'emails/class-give-email-tags.php' );
		$this->bindInstance( 'html', 'Give_HTML_Elements', 'admin/class-give-html-elements.php', true );
		$this->bindInstance( 'donors', 'Give_DB_Donors', 'database/class-give-db-donors.php' );
		$this->bindInstance( 'donor_meta', 'Give_DB_Donor_Meta', 'database/class-give-db-donor-meta.php' );
		$this->bindInstance( 'tooltips', 'Give_Tooltips', 'class-give-tooltips.php' );
		$this->bindInstance( 'notices', 'Give_Notices', 'class-notices.php' );
		$this->bindInstance( 'payment_meta', 'Give_DB_Payment_Meta', 'database/class-give-db-payment-meta.php' );
		$this->bindInstance( 'log_db', 'Give_DB_Logs', 'database/class-give-db-logs.php' );
		$this->bindInstance( 'logmeta_db', 'Give_DB_Log_Meta', 'database/class-give-db-logs-meta.php' );
		$this->bindInstance( 'logs', 'Give_Logging', 'class-give-logging.php' );
		$this->bindInstance( 'form_meta', 'Give_DB_Form_Meta', 'database/class-give-db-form-meta.php' );
		$this->bindInstance( 'sequential_donation_db', 'Give_DB_Sequential_Ordering', 'database/class-give-db-sequential-ordering.php' );
		$this->bindInstance( 'async_process', 'Give_Async_Process', 'class-give-async-process.php' );
		$this->bindInstance( 'scripts', 'Give_Scripts', 'class-give-scripts.php' );
		$this->bindInstance( 'seq_donation_number', 'Give_Sequential_Donation_Number', 'payments/class-give-sequential-donation-number.php', true );
		$this->bindInstance( 'comment', 'Give_Comment', 'class-give-comment.php', true );
		$this->bindInstance( 'session_db', 'Give_DB_Sessions', 'database/class-give-db-sessions.php' );
		$this->bindInstance( 'session', 'Give_Session', 'class-give-session.php', true );
	}

	/**
	 * A helper for loading legacy classes that do not use autoloading, then binding their instance
	 * to the container.
	 *
	 * @since 2.8.0
	 *
	 * @param string         $alias
	 * @param string|Closure $class
	 * @param string         $includesPath
	 * @param bool           $singleton
	 */
	private function bindInstance( $alias, $class, $includesPath, $singleton = false ) {
		require_once GIVE_PLUGIN_DIR . "includes/$includesPath";

		if ( $class instanceof Closure ) {
			give()->instance( $alias, $class() );
		} elseif ( $singleton ) {
			give()->instance( $alias, $class::get_instance() );
		} else {
			give()->instance( $alias, new $class() );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @since 2.8.0
	 *
	 * @param string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
			case 'wpcli':
				return defined( 'WP_CLI' ) && WP_CLI;
		}
	}
}
