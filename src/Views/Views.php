<?php

/**
 * Admin Views Class
 *
 * @package Give
 */

namespace Give\Views;

defined('ABSPATH') || exit;

/**
 * Manages Views
 *
 */
class Views {

    /**
     * Initialize Reports and Pages, register hooks
     */
    public function init() {
        // To prevent conflict on we are loading autoload.php when need for now. In future we can loaded it globally.
        require GIVE_PLUGIN_DIR . 'vendor/autoload.php';

        // Load admin views
		$this->load_admin_views();
    }

    public function __construct() {
        //Do nothing
	}

	public function load_admin_views() {

		//require GIVE_PLUGIN_DIR . 'src/Views/Admin/Admin.php';

		// Load payment statuses endpoint
		$admin = new Admin();
		$admin->init();

	}

}
$views = new Views;
$views->init();
