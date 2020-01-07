<?php
/**
 * Reports class
 *
 * @package Give
 */

namespace Give\Reports;

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
		// To prevent conflict on we are loading autoload.php when need for now. In future we can loaded it globally.
		require GIVE_PLUGIN_DIR . 'vendor/autoload.php';

		$this->reports = [
			'payments' => new Report\Payments(),
			'donors' => new Report\Donors(),
			'campaigns' => new Report\Campaigns(),
		];


		$admin = new AdminView();
		$admin->init();


		$api = new API([
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
