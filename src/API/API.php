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
	 * Array of controllers to match each endpoint
	 * See `init` for structure of the data and setup process
	 *
	 * @var array
	 */

	protected $controllers = [];

	/**
	 * Initialize Reports and Pages, register hooks
	 */
	public function init() {
		// To prevent conflict on we are loading autoload.php when need for now. In future we can loaded it globally.
		require GIVE_PLUGIN_DIR . 'vendor/autoload.php';

		$this->controllers = [
			'reports' => new Controller\Reports(),
		];
	}

	public function __construct() {
		//Do nothing
	}
}