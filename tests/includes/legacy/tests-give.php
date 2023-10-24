<?php

/**
 * Class Tests_Give
 */
class Tests_Give extends Give_Unit_Test_Case {
	protected $object;

	public function setUp() {
		parent::setUp();
		$this->object = Give();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @covers Give::setup_constants
	 */
	public function test_constants() {

		// Adjust for relative directory structure.
		$filePath = dirname( dirname( dirname( __FILE__ ) ) );
		// Plugin Folder URL
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_url( $filePath ) );
		$this->assertSame( GIVE_PLUGIN_URL, $path );

		// Plugin Folder Path
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_path( $filePath ) );
		$this->assertSame( GIVE_PLUGIN_DIR, $path );

		// Plugin Root File
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_path( $filePath ) );
		$this->assertSame( GIVE_PLUGIN_FILE, $path . 'give.php' );
	}

	/**
	 * @covers Give::includes
	 */
	public function test_includes() {

		/** Check Includes Exist */
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/post-types.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-scripts.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/ajax-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-roles.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-template-loader.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-donate-form.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db-comments.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db-comments-meta.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db-donor-meta.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db-donors.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db-form-meta.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db-meta.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db-payment-meta.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db-sequential-ordering.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/database/class-give-db-sessions.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-donor.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-session.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/class-give-html-elements.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-logging.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-license-handler.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/country-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/template-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/misc-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/import-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/forms/functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/forms/template.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/forms/widget.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/shortcodes.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/formatting.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/currency-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/currencies-list.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/error-tracking.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/price-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/process-donation.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/login-register.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-tooltips.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/user-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/payments/functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/payments/actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/payments/class-payment-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/payments/class-payments-query.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/gateways/functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/gateways/actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/gateways/paypal/paypal-standard.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/gateways/offline-donations.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/emails/class-give-emails.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/emails/class-give-email-tags.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/emails/functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/emails/template.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/emails/actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-notices.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/donors/class-give-donors-query.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/libraries/give-pdf.php' );

		/** Check Admin Exist */
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/admin-footer.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/admin-pages.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/class-i18n-module.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/payments/actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/payments/payments-history.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/donors/donors.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/donors/donor-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/donors/donor-actions.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/forms/metabox.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/forms/class-give-form-duplicator.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/forms/class-metabox-form-data.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/forms/dashboard-columns.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-api.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-data.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-export.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-import.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-logs.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-system-info.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-delete-import-donors.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-delete-test-transactions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-all-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-donor-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-form-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-income.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-single-donor-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-reset-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/tools-actions.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/import/class-give-import-core-settings.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/import/class-give-import-donations.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export-donors.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-give-export-donations.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/give-export-donations-exporter.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export-earnings.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/export-actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/export-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/pdf-reports.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/give-export-donations-functions.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-data.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-exports.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-system-info.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-import-core-settings.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-import-donations.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/reports/reports.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/reports/class-give-graph.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/reports/graphing.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/abstract-shortcode-generator.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/class-shortcode-button.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-donation-history.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-form.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-goal.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-login.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-profile-editor.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-receipt.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-register.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-totals.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/upgrades/class-give-updates.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/gateways/stripe/class-give-stripe.php' );
	}
}
