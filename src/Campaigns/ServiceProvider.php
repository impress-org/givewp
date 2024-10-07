<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\AddCampaignFormFromRequest;
use Give\Campaigns\Actions\CreateDefaultCampaignForm;
use Give\Campaigns\Actions\DeleteCampaignPage;
use Give\Campaigns\Migrations\MigrateFormsToCampaignForms;
use Give\Campaigns\Migrations\P2P\SetCampaignType;
use Give\Campaigns\Migrations\Tables\CreateCampaignFormsTable;
use Give\Campaigns\Migrations\Tables\CreateCampaignsTable;
use Give\DonationForms\V2\DonationFormsAdminPage;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     * @inheritDoc
     */
    public function register(): void
    {
        $this->registerTableNames();
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->registerMenus();
        $this->registerActions();
        $this->setupCampaignPages();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerCampaignEntity();
        $this->setupCampaignForms();
    }

    /**
     * @unreleased
     */
    private function registerRoutes()
    {
        Hooks::addAction('rest_api_init', Routes\RegisterCampaignRoutes::class);
        Hooks::addAction('rest_api_init', Routes\GetCampaignsListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\DeleteCampaignListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetCampaignStatistics::class, 'registerRoute');
    }

    /**
     * @unreleased
     */
    private function registerMigrations(): void
    {
        give(MigrationsRegister::class)->addMigrations(
            [
                CreateCampaignsTable::class,
                SetCampaignType::class,
                CreateCampaignFormsTable::class,
                MigrateFormsToCampaignForms::class,
            ]
        );
    }

    /**
     * @unreleased
     */
    private function registerTableNames(): void
    {
        global $wpdb;

        $wpdb->give_campaigns = $wpdb->prefix . 'give_campaigns';
        $wpdb->give_campaign_forms = $wpdb->prefix . 'give_campaign_forms';
    }

    /**
     * @unreleased
     */
    private function registerActions(): void
    {
        Hooks::addAction('givewp_campaign_deleted', DeleteCampaignPage::class);
    }

    /**
     * @unreleased
     */
    private function registerMenus()
    {
        Hooks::addAction('admin_menu', CampaignsAdminPage::class, 'addCampaignsSubmenuPage', 999);
    }

    private function setupCampaignPages()
    {
        Hooks::addAction('init', Actions\RegisterCampaignPagePostType::class);
        Hooks::addAction('admin_action_edit_campaign_page', Actions\EditCampaignPageRedirect::class);
    }

    /**
     * @unreleased
     */
    private function registerCampaignEntity()
    {
        Hooks::addAction('init', Actions\RegisterCampaignEntity::class);
    }

    /**
     * @unreleased
     */
    private function setupCampaignForms()
    {
        if (CampaignsAdminPage::isShowingDetailsPage()) {
            Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadScripts');

            // Temp solution to load "donations" and "revenue" columns on the "Forms" tab
            if ( ! define('GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS')) {
                define('GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS', false);
            }
        }

        Hooks::addAction('givewp_donation_form_created', AddCampaignFormFromRequest::class);
        Hooks::addAction('givewp_campaign_created', CreateDefaultCampaignForm::class);
    }
}
