<?php

/**
 * Admin Views Class
 *
 * @package Give
 */

namespace Give\Views;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Views
 */
class Views {

	/**
	 * Initialize admin and frontend views
	 */
	public function init() {
		// Load admin views
		$this->load_admin_views();
	}

	public function __construct() {
		// Do nothing
	}

	public function load_admin_views() {
		// Init admin views
		$admin = new Admin();
		$admin->init();
	}

}
