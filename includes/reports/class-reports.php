<?php
/**
 * Reports class
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

/**
 * Manages reports
 */
class Reports {

	/**
	 * Gathers and sets up information for reports page
	 * See `init` for structure of the data and setup process
	 *
	 * @var array
	 */

	protected $reports = [];
	protected $pages = [];

	/**
	 * Initialize Reports and Pages, register hooks
	 */
	public function init() {

		require_once GIVE_PLUGIN_DIR . 'includes/reports/class-reports-admin.php';

		$admin = new Reports_Admin($this->pages);
		$admin->init();

		require_once GIVE_PLUGIN_DIR . 'includes/reports/class-reports-api.php';

		$api = new Reports_API([
			'reports' => $this->reports,
			'pages' => $this->pages
		]);
		$api->init();

	}

	public function __construct() {

		//Require reports
		require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-donors-report.php';
		require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-payments-report.php';
		require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-campaigns-report.php';

		//Require pages
		require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-overview-page.php';
		require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-donors-page.php';
		require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-single-page.php';

		$this->reports = [
            'payments' => new Payments_Report(),
			'donors' => new Donors_Report(),
			'campaigns' => new Campaigns_Report(),
		];

		$this->pages = [
			'overview' => new Overview_Page(),
			'donors' => new Donors_Page(),
			'single' => new Single_Page()
		];

		$this->init();
	}
}
$reports = new Reports;
$reports->init();