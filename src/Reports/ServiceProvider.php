<?php

namespace Give\Reports;

use Give\Reports\Endpoints\BaseEndpoint;

class ServiceProvider implements \Give\ServiceProviders\ServiceProvider {
	/**
	 * @var string[] array of RestRoute classes
	 */
	private $reportRoutes = [
		Endpoints\PaymentStatuses::class,
		Endpoints\PaymentMethods::class,
		Endpoints\FormPerformance::class,
		Endpoints\TopDonors::class,
		Endpoints\RecentDonations::class,
		Endpoints\Income::class,
		Endpoints\IncomeBreakdown::class,
		Endpoints\AverageDonation::class,
		Endpoints\TotalDonors::class,
		Endpoints\TotalIncome::class,
		Endpoints\TotalRefunds::class,
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_action( 'rest_api_init', [ $this, 'registerRoutes' ] );
	}

	/**
	 * Calls the route registrations within the WordPress REST API hook
	 *
	 * @since 2.8.0
	 */
	public function registerRoutes() {
		foreach ( $this->reportRoutes as $route ) {
			/** @var BaseEndpoint $route */
			$route = give()->make( $route );

			$route->registerRoute();
		}
	}
}
