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

	public $reports = [];

	/**
	 * Initialize Reports and Pages, register hooks
	 */
	public function init() {
		// To prevent conflict on we are loading autoload.php when need for now. In future we can loaded it globally.
		require GIVE_PLUGIN_DIR . 'vendor/autoload.php';

		$admin = new AdminView();
		$admin->init();

	}

	public function get_reports() {
		return $this->reports;
	}

	public function __construct() {
		//Do nothing
	}
}
$reports = new Reports;
$reports->init();
