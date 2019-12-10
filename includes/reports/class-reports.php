<?php
/**
 * Reports manager
 *
 * @package Give
 */

namespace Give;

require_once GIVE_PLUGIN_DIR . 'includes/reports/class-report.php';
require_once GIVE_PLUGIN_DIR . 'includes/reports/class-chart.php';
require_once GIVE_PLUGIN_DIR . 'includes/admin/class-page.php';

defined( 'ABSPATH' ) || exit;

/**
 * Manages the settings.
 */
class Reports {

	/**
	 * Information about all of the APIs.
	 * See `init` for structure of the data.
	 *
	 * @var array
	 */

	public static $reports = [];
	public static $charts = [];
	public static $pages = [];

	public static $period = [];

	/**
	 * Initialize and register all of the post types
	 */
	public static function init() {
		static::$reports = [
            'payments' => new Payments_Report(['period' => static::$period]),
			'donors' => new Donors_Report(['period' => static::$period]),
			'campaigns' => new Campaigns_Report(['period' => static::$period]),
		];

		static::$pages = [
			'overview' => new Page ([
				'title' => 'Overview',
				'charts' => [
					'donations_for_period' => new Chart([
						'title' => 'Donations For Period',
						'type' => 'line',
						'width' => 12,
						'data' => [
							[
								'label' => 'Total Raised',
								'source' => static::$reports['payments']->get_total('payments')
							],
							[
								'label' => 'Total Donors',
								'source' => static::$reports['donors']->get_total('donors')
							],
							[
								'label' => 'Average Donation',
								'source' => static::$reports['payments']->get_average('payments')
							],
							[
								'label' => 'Total Refunded',
								'source' => static::$reports['payments']->get_total('refunds')
							]
						]
					]),
					'campaign_performance' => new Chart([
						'title' => 'Campaign Performance',
						'type' => 'doughnut',
						'width' => 6,
						'data' => static::$reports['campaigns']->get_totals([
							'total' => 'payments',
							'showing' => 4,
							'sortby' => 'ASC'
						])
					]),
					'payment_statuses' => new Chart ([
						'title' => 'Payment Statuses',
						'type' => 'bar',
						'width' => 6,
						'data' => static::$reports['payments']->get_totals([
							'total' => 'statuses',
						])
					]),
				],
			])
		];

		add_action( 'init', [__CLASS__, 'register_reports'] );

	}

    /**
	 * Register all reports
	 */
	public static function register_reports() {
		foreach (static::$reports as $report) {
			$report->register_report();
		}
	}

	/**
	 * Register all pages
	 */
	public static function register_pages() {
		foreach (static::$pages as $page) {
			$page->register_page();
		}
	}
}
Reports::init();