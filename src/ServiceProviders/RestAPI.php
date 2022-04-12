<?php

namespace Give\ServiceProviders;

use Give\DonationForms\Endpoints\ListForms;
use Give\Donations\Endpoints\DonationActions;
use Give\Donations\Endpoints\ListDonations;
use Give\Donors\Endpoints\SwitchDonorView;
use Give\Donations\Endpoints\SwitchDonationView;
use Give\DonationForms\Endpoints\SwitchDonationFormView;
use Give\Donors\Endpoints\DeleteDonor;
use Give\Donors\Endpoints\ListDonors;
use Give\DonationForms\Endpoints\FormActions;
use Give\API\Endpoints\Logs\FlushLogs;
use Give\API\Endpoints\Logs\GetLogs;
use Give\API\Endpoints\Migrations\GetMigrations;
use Give\API\Endpoints\Migrations\RunMigration;
use Give\API\Endpoints\Reports\AverageDonation;
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
use Give\API\RestRoute;

class RestAPI implements ServiceProvider
{
    /**
     * @var string[] array of RestRoute classes
     */
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
        GetLogs::class,
        FlushLogs::class,
        ListForms::class,
        ListDonors::class,
        ListDonations::class,
        SwitchDonorView::class,
        SwitchDonationView::class,
        SwitchDonationFormView::class,
        DonationActions::class,
        DeleteDonor::class,
        FormActions::class,
        GetMigrations::class,
        RunMigration::class,
    ];

    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Calls the route registrations within the WordPress REST API hook
     *
     * @since 2.8.0
     */
    public function registerRoutes()
    {
        foreach ($this->reportRoutes as $route) {
            /** @var RestRoute $route */
            $route = give()->make($route);

            $route->registerRoute();
        }
    }
}
