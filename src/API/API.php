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

        // Register Reports Routes
        $reports = new Endpoints\Reports();
        $reports->init();
    }

    public function __construct() {
        //Do nothing
    }

    public function setup_endpoints() {
        // Register Reports Routes
        $reports = new Endpoints\Reports();
		$reports->init();
    }
}
$api = new API;
$api->init();
