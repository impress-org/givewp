<?php

/**
 * AdminViews Class
 *
 * @package Give
 */

namespace Give\Views;

defined( 'ABSPATH' ) || exit;

/**
 * Manages Admin Views
 */
class Admin {

	/**
	 * Initialize Admin views
	 */
	public function init() {

		// Load dashboard widgets
		$this->load_dashboard_widgets();

		// Load pages
		$this->load_pages();

	}

	public function __construct() {
		// Do nothing
	}

	public function load_dashboard_widgets() {
		// Load Reports dashboard widget
		$reports = new Admin\DashboardWidgets\Reports();
		$reports->init();
	}

	public function load_pages() {
		// Load Reports page
		$reports = new Admin\Pages\Reports();
		$reports->init();
	}

}
