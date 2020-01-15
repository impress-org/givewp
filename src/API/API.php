<?php

/**
 * API class
 *
 * @package Give
 */

namespace Give\API;

defined('ABSPATH') || exit;

/**
 * Manages API Endpoints
 */
class API {

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
        //Do nothing
	}

	public function load_endpoints() {
		// Load payment statuses endpoint
		$paymentStatuses = new Endpoints\Reports\PaymentStatuses();
		$paymentStatuses->init();

		//Load payment methods endpoint
		$paymentMethods = new Endpoints\Reports\PaymentMethods();
		$paymentMethods->init();

		// Load form performance endpoint
		$formPerformance = new Endpoints\Reports\FormPerformance();
		$formPerformance->init();

		// Load top donors endpoint
		$topDonors = new Endpoints\Reports\TopDonors();
		$topDonors->init();
	}

}
$api = new API;
$api->init();
