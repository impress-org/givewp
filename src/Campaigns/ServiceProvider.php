<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\DeleteCampaignPage;
use Give\Campaigns\Migrations\MigrateFormsToCampaignForms;
use Give\Campaigns\Migrations\P2P\SetCampaignType;
use Give\Campaigns\Migrations\Tables\CreateCampaignFormsTable;
use Give\Campaigns\Migrations\Tables\CreateCampaignsTable;
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
    }

    /**
     * @unreleased
     */
    private function registerRoutes()
    {
        Hooks::addAction('rest_api_init', Routes\CreateCampaign::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetCampaignsListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\DeleteCampaignListTable::class, 'registerRoute');
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
}
