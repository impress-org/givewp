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
	 * @covers Give
	 */
	public function test_give_instance() {
		$this->assertClassHasStaticAttribute( '_instance', 'Give' );
	}

	/**
	 * @covers Give::setup_constants
	 */
	public function test_constants() {
		// Plugin Folder URL
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_url( __FILE__ ) );
		$this->assertSame( GIVE_PLUGIN_URL, $path );

		// Plugin Folder Path
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_path( __FILE__ ) );
		$this->assertSame( GIVE_PLUGIN_DIR, $path );

		// Plugin Root File
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_path( __FILE__ ) );
		$this->assertSame( GIVE_PLUGIN_FILE, $path . 'give.php' );
	}

	/**
	 * @covers Give::includes
	 */
	public function test_includes() {

		/** Check Includes Exist */
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/post-types.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/scripts.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/ajax-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-roles.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-template-loader.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-donate-form.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-db.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-db-meta.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-db-donor-meta.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-db-donors.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-donor.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-session.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-html-elements.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-logging.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/class-give-license-handler.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/country-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/template-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/misc-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/import-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/forms/functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/forms/template.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/forms/widget.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/shortcodes.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/formatting.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/currency-functions.php' );
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
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/gateways/paypal-standard.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/gateways/offline-donations.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/gateways/manual.php' );
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
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/welcome.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/admin-pages.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/class-i18n-module.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/add-ons.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/payments/actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/payments/payments-history.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/donors/donors.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/donors/donor-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/donors/donor-actions.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/forms/metabox.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/forms/dashboard-columns.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-api.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-data.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-export.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-logs.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/class-settings-system-info.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-delete-test-transactions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-all-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-donor-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-form-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-income.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-single-donor-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-reset-stats.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/data/tools-actions.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export-donors.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export-forms.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export-payments.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export-earnings.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/export-actions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/export-functions.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/export/pdf-reports.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/class-api-requests-logs-list-table.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/class-gateway-error-logs-list-table.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/class-sales-logs-list-table.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/logs.php' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-data.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-exports.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/tools/views/html-admin-page-system-info.php' );

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

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/upgrades/class-give-updates.php' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php' );

		/** Check Assets Exist */
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/css/chosen.css' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/css/chosen.min.css' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/css/chosen-sprite.png' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/css/give-admin.css' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/css/give-admin.css.map' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/css/give-admin.min.css' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/css/jquery-ui-fresh.css' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/css/jquery-ui-fresh.min.css' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/addons.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/dashboard.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/donors.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/forms.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/give-admin.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/logs.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/payment-history.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/reports.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/settings.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/admin/welcome.scss' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/frontend/_mixins.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/frontend/_variables.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/frontend/fonts.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/frontend/forms.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/frontend/give-frontend.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/frontend/layouts.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/frontend/progress-bar.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/frontend/receipt.scss' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/plugins/_settings.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/plugins/float-labels.scss' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/scss/plugins/magnific-popup.scss' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/fonts/icomoon.eot' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/fonts/icomoon.svg' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/fonts/icomoon.woff' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/admin/admin-forms.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/admin/admin-forms.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/admin/admin-scripts.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/admin/admin-scripts.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/admin/admin-widgets.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/admin/admin-widgets.min.js' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/frontend/give.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/frontend/give.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/frontend/give.all.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/frontend/give-ajax.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/frontend/give-ajax.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/frontend/give-donations.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/frontend/give-donations.min.js' );

		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/chosen.jquery.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/chosen.jquery.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/float-labels.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/float-labels.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.magnific-popup.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.magnific-popup.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.blockUI.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.blockUI.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.payment.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.payment.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.flot.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.flot.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.flot.orderBars.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.flot.orderBars.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.flot.time.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.flot.time.min.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.payment.js' );
		$this->assertFileExists( GIVE_PLUGIN_DIR . 'assets/js/plugins/jquery.payment.min.js' );

	}
}
