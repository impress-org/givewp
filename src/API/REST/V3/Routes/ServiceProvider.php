<?php

namespace Give\API\REST\V3\Routes;

use Give\API\REST\V3\Routes\Campaigns\GetCampaignComments;
use Give\API\REST\V3\Routes\Campaigns\GetCampaignRevenue;
use Give\API\REST\V3\Routes\Campaigns\GetCampaignStatistics;
use Give\API\REST\V3\Routes\Campaigns\RegisterCampaignRoutes;
use Give\API\REST\V3\Routes\Donations\DonationController;
use Give\API\REST\V3\Routes\Donations\DonationNotesController;
use Give\API\REST\V3\Routes\Donors\DonorController;
use Give\API\REST\V3\Routes\Donors\DonorNotesController;
use Give\API\REST\V3\Routes\Donors\DonorStatisticsController;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 4.2.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 4.2.0
     */
    public function register()
    {
        // TODO: Implement register() method.
    }

    /**
     * @since 4.4.0 Load donors route and register CURIE
     * @since 4.2.0
     */
    public function boot()
    {
        Hooks::addFilter('rest_response_link_curies', CURIE::class, 'registerCURIE');

        $this->loadCampaignsRoutes();
        $this->loadDonorsRoutes();
        $this->loadDonationsRoutes();
    }

    /**
     * @since 4.2.0
     */
    private function loadCampaignsRoutes()
    {
        Hooks::addAction('rest_api_init', RegisterCampaignRoutes::class);
        Hooks::addAction('rest_api_init', GetCampaignStatistics::class, 'registerRoute');
        Hooks::addAction('rest_api_init', GetCampaignRevenue::class, 'registerRoute');
        Hooks::addAction('rest_api_init', GetCampaignComments::class, 'registerRoute');
    }

    /**
     * @since 4.4.0
     */
    private function loadDonorsRoutes()
    {
        add_action('rest_api_init', function () {
            $donorController = new DonorController();
            $donorController->register_routes();

            $donorStatisticsController = new DonorStatisticsController();
            $donorStatisticsController->register_routes();

            $donorNotesController = new DonorNotesController();
            $donorNotesController->register_routes();
        });
    }

    /**
     * @since 4.4.0
     */
    private function loadDonationsRoutes()
    {
        add_action('rest_api_init', function () {
            $donationsController = new DonationController();
            $donationsController->register_routes();

            $donationNotesController = new DonationNotesController();
            $donationNotesController->register_routes();
        });
    }
}
