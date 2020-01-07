<?php
/**
 * Reports class
 *
 * @package Give
 */

namespace Give\API;

defined( 'ABSPATH' ) || exit;

/**
 * Manages API Endpoints
 */
class API {
    
    /**
	 * WP REST API Namespace 
	 * @var string
	 */

    protected $namespace = 'give-api/v2';

	/**
	 * Initialize Reports and Pages, register hooks
	 */
	public function init() {
		// To prevent conflict on we are loading autoload.php when need for now. In future we can loaded it globally.
		require GIVE_PLUGIN_DIR . 'vendor/autoload.php';
        
        $this->register_routes();
	}

	public function __construct() {
		//Do nothing
    }
    
    public function register_routes() {
        
        // Register Reports Routes
        $reports = new Controllers\Reports();
        $reports->register_routes();

    }
}
$api = new API;
$api->init();