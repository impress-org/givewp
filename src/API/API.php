<?php

/**
 * API class
 *
 * @package Give
 */

namespace Give\API;

use Give\API\Endpoints\Reports as Reports;

defined( 'ABSPATH' ) || exit;

/**
 * Manages API Endpoints
 */
class API {

	protected $endpoints = [
		Reports\PaymentStatuses::class,
		Reports\PaymentMethods::class,
		Reports\FormPerformance::class,
		Reports\TopDonors::class,
		Reports\RecentDonations::class,
		Reports\Income::class,
		Reports\IncomeBreakdown::class,
		Reports\AverageDonation::class,
		Reports\TotalDonors::class,
		Reports\TotalIncome::class,
		Reports\TotalRefunds::class,
	];

	/**
	 * Initialize Reports and Pages, register hooks
	 */
	public function init() {
		// To prevent conflict on we are loading autoload.php when need for now. In future we can loaded it globally.
		require GIVE_PLUGIN_DIR . 'vendor/autoload.php';

		// Load endpoints
		$this->load_endpoints();

	}

	public function __construct() {
		// Do nothing
	}

	public function load_endpoints() {
		foreach ( $this->endpoints as $endpoint ) {
			$class = new $endpoint();
			$class->init();
		}
	}

}
$api = new API();
$api->init();
