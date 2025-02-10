<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\AddCampaignFormFromRequest;
use Give\Campaigns\Actions\CreateDefaultCampaignForm;
use Give\Campaigns\Actions\DeleteCampaignPage;
use Give\Campaigns\Actions\FormInheritsCampaignGoal;
use Give\Campaigns\Migrations\Donations\AddCampaignId as DonationsAddCampaignId;
use Give\Campaigns\Migrations\MigrateFormsToCampaignForms;
use Give\Campaigns\Migrations\P2P\SetCampaignType;
use Give\Campaigns\Migrations\RevenueTable\AddCampaignID as RevenueTableAddCampaignID;
use Give\Campaigns\Migrations\RevenueTable\AddIndexes;
use Give\Campaigns\Migrations\RevenueTable\AssociateDonationsToCampaign;
use Give\Campaigns\Migrations\Tables\CreateCampaignFormsTable;
use Give\Campaigns\Migrations\Tables\CreateCampaignsTable;
use Give\Campaigns\Repositories\CampaignRepository;
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
        give()->singleton('campaigns', CampaignRepository::class);
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
        $this->registerCampaignBlocks();
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
        Hooks::addAction('rest_api_init', Routes\GetCampaignRevenue::class, 'registerRoute');
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
                RevenueTableAddCampaignID::class,
                AssociateDonationsToCampaign::class,
                AddIndexes::class,
                DonationsAddCampaignId::class
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
        Hooks::addAction('givewp_donation_form_creating', FormInheritsCampaignGoal::class);
        Hooks::addAction('give_form_duplicated', Actions\AssignDuplicatedFormToCampaign::class, '__invoke', 10, 2);
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
        }

        /**
         * We implemented a feature to load the stats columns ("Goal", "Donations" and "Revenue") using an async approach,
         * so we could prevent a long page load on websites with lots of forms. However, the campaign details page's current
         * "Forms" tab still doesn't support it. Still, it's using the same Form List Table that active the async approach by
         * default, so the line below is necessary to disable it while we still don't have support for async loading on this screen.
         *
         * @see https://github.com/impress-org/givewp/pull/7483
         */
        if ( ! defined('GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS')) {
            define('GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS', false);
        }

        Hooks::addAction('save_post_give_forms', AddCampaignFormFromRequest::class, 'optionBasedFormEditor', 10, 3);
        Hooks::addAction('givewp_donation_form_created', AddCampaignFormFromRequest::class, 'visualFormBuilder');
        Hooks::addAction('givewp_campaign_created', CreateDefaultCampaignForm::class);
    }

    /**
     * @unreleased
     */
    private function registerCampaignBlocks()
    {
        Hooks::addAction('rest_api_init', Actions\RegisterCampaignIdRestField::class);
        Hooks::addAction('init', Actions\RegisterCampaignBlocks::class);
    }
}
