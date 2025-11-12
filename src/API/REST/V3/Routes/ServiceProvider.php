<?php

namespace Give\API\REST\V3\Routes;

use Give\API\REST\V3\Entities\Actions\RegisterAdminEntities;
use Give\API\REST\V3\Entities\Actions\RegisterPublicEntities;
use Give\API\REST\V3\Routes\Campaigns\CampaignController;
use Give\API\REST\V3\Routes\Campaigns\CampaignCommentsController;
use Give\API\REST\V3\Routes\Campaigns\CampaignPageController;
use Give\API\REST\V3\Routes\Campaigns\CampaignRevenueController;
use Give\API\REST\V3\Routes\Campaigns\CampaignStatisticsController;
use Give\API\REST\V3\Routes\Donations\DonationController;
use Give\API\REST\V3\Routes\Donations\DonationNotesController;
use Give\API\REST\V3\Routes\Donors\DonorController;
use Give\API\REST\V3\Routes\Donors\DonorNotesController;
use Give\API\REST\V3\Routes\Donors\DonorStatisticsController;
use Give\API\REST\V3\Routes\Subscriptions\SubscriptionController;
use Give\API\REST\V3\Routes\Subscriptions\SubscriptionNotesController;
use Give\API\REST\V3\Support\CURIE;
use Give\Campaigns\Actions\RegisterCampaignEntity;
use Give\DonationForms\Actions\RegisterFormEntity;
use Give\DonationForms\Routes\DonationFormsEntityRoute;
use Give\Donations\Actions\RegisterDonationEntity;
use Give\Donors\Actions\RegisterDonorEntity;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Subscriptions\Actions\RegisterSubscriptionEntity;

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
        $this->loadSubscriptionsRoutes();
        $this->loadDonationFormsRoutes();
        $this->registerEntities();
    }


    /**
     * @unreleased
     */
    private function registerEntities()
    {
        Hooks::addAction('admin_enqueue_scripts', RegisterAdminEntities::class);
        Hooks::addAction('wp_enqueue_scripts', RegisterPublicEntities::class);
    }

    /**
     * @unreleased updated to use REST controllers
     * @since 4.2.0
     */
    private function loadCampaignsRoutes()
    {
        add_action(
            'rest_api_init',
            function () {
                $campaignController = new CampaignController();
                $campaignController->register_routes();


                $campaignCommentsController = new CampaignCommentsController();
                $campaignCommentsController->register_routes();

                $campaignPageController = new CampaignPageController();
                $campaignPageController->register_routes();

                $campaignRevenueController = new CampaignRevenueController();

                $campaignRevenueController->register_routes();

                $campaignStatisticsController = new CampaignStatisticsController();
                $campaignStatisticsController->register_routes();
            }
        );
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

    /**
     * @since 4.8.0
     */
    private function loadSubscriptionsRoutes()
    {
        add_action('rest_api_init', function () {
            $subscriptionsController = new SubscriptionController();
            $subscriptionsController->register_routes();

            $subscriptionNotesController = new SubscriptionNotesController();
            $subscriptionNotesController->register_routes();
        });
    }

    /**
     * @since 4.2.0
     * @unreleased
     */
    private function loadDonationFormsRoutes()
    {
        Hooks::addAction('rest_api_init', DonationFormsEntityRoute::class);
    }
}
