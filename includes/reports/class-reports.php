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

		// Require reports
		require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-payment-statuses-report.php';

		// Register reports (keys used by Reports API as endpoints)
		$this->reports = [
			'payment-statuses' => new Payment_Statuses_Report(),
		];

		// Initialize Reports admin area
		require_once GIVE_PLUGIN_DIR . 'includes/reports/class-reports-admin.php';

		$admin = new Reports_Admin();
		$admin->init();


		// Initialize Reports API
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