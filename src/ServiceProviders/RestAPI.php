<?php

namespace Give\ServiceProviders;

use Give\API\Endpoints\Reports\AverageDonation;
use Give\API\Endpoints\Reports\Endpoint;
use Give\API\Endpoints\Reports\FormPerformance;
use Give\API\Endpoints\Reports\Income;
use Give\API\Endpoints\Reports\IncomeBreakdown;
use Give\API\Endpoints\Reports\PaymentMethods;
use Give\API\Endpoints\Reports\PaymentStatuses;
use Give\API\Endpoints\Reports\RecentDonations;
use Give\API\Endpoints\Reports\TopDonors;
use Give\API\Endpoints\Reports\TotalDonors;
use Give\API\Endpoints\Reports\TotalIncome;
use Give\API\Endpoints\Reports\TotalRefunds;

class RestAPI implements ServiceProvider {
	private $reportRoutes = [
		PaymentStatuses::class,
		PaymentMethods::class,
		FormPerformance::class,
		TopDonors::class,
		RecentDonations::class,
		Income::class,
		IncomeBreakdown::class,
		AverageDonation::class,
		TotalDonors::class,
		TotalIncome::class,
		TotalRefunds::class,
	];

	public function register() {
	}

	public function boot() {
		add_action( 'rest_api_init', [ $this, 'registerRoutes' ] );
	}

	public function registerRoutes() {
		foreach ( $this->reportRoutes as $route ) {
			/** @var Endpoint $route */
			$route = give()->make( $route );

			$route->registerRoute();
		}
	}
}
