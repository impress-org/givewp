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

	/**
	 * Initialize Reports and Pages, register hooks
	 */
	public function init() {

		//Require reports
		require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-donors-report.php';
		require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-payments-report.php';
		require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-campaigns-report.php';

		$this->reports = [
			'payments' => new Payments_Report(),
			'donors' => new Donors_Report(),
			'campaigns' => new Campaigns_Report(),
		];

		require_once GIVE_PLUGIN_DIR . 'includes/reports/class-reports-admin.php';

		$admin = new Reports_Admin($this->pages);
		$admin->init();

		require_once GIVE_PLUGIN_DIR . 'includes/reports/class-reports-api.php';

		$api = new Reports_API([
			'reports' => $this->reports,
		]);
		$api->init();

	}

	public function __construct() {
		//Do nothing
	}
}
$reports = new Reports;
$reports->init();