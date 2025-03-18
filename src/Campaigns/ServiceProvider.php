<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\AddCampaignFormFromRequest;
use Give\Campaigns\Actions\AddNewBadgeToAdminMenuItem;
use Give\Campaigns\Actions\AssociateCampaignPageWithCampaign;
use Give\Campaigns\Actions\CreateCampaignPage;
use Give\Campaigns\Actions\CreateDefaultCampaignForm;
use Give\Campaigns\Actions\DeleteCampaignPage;
use Give\Campaigns\Actions\FormInheritsCampaignGoal;
use Give\Campaigns\Actions\LoadCampaignOptions;
use Give\Campaigns\Actions\RedirectLegacyCreateFormToCreateCampaign;
use Give\Campaigns\Actions\ReplaceGiveFormsCptLabels;
use Give\Campaigns\AsyncData\AdminCampaignListView\AdminCampaignListView;
use Give\Campaigns\ListTable\Columns\DonationsCountColumn;
use Give\Campaigns\ListTable\Columns\GoalColumn;
use Give\Campaigns\ListTable\Columns\RevenueColumn;
use Give\Campaigns\Migrations\Donations\AddCampaignId as DonationsAddCampaignId;
use Give\Campaigns\Migrations\MigrateFormsToCampaignForms;
use Give\Campaigns\Migrations\P2P\SetCampaignType;
use Give\Campaigns\Migrations\RevenueTable\AddCampaignID as RevenueTableAddCampaignID;
use Give\Campaigns\Migrations\RevenueTable\AddIndexes;
use Give\Campaigns\Migrations\RevenueTable\AssociateDonationsToCampaign;
use Give\Campaigns\Migrations\Tables\CreateCampaignFormsTable;
use Give\Campaigns\Migrations\Tables\CreateCampaignsTable;
use Give\Campaigns\Models\Campaign;
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
        $this->replaceGiveFormsCptLabels();
        $this->registerActions();
        $this->setupCampaignPages();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerCampaignEntity();
        $this->registerCampaignBlocks();
        $this->setupCampaignForms();
        $this->loadCampaignOptions();
        $this->addNewBadgeToMenu();
        $this->registerAsyncData();
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
        Hooks::addAction('rest_api_init', Routes\GetCampaignComments::class, 'registerRoute');
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
                DonationsAddCampaignId::class,
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
        Hooks::addAction('givewp_campaign_page_created', AssociateCampaignPageWithCampaign::class);
        Hooks::addAction('give_form_duplicated', Actions\AssignDuplicatedFormToCampaign::class, '__invoke', 10, 2);
        Hooks::addAction('init', Actions\CampaignPageTemplate::class, 'registerTemplate');
        Hooks::addFilter('template_include', Actions\CampaignPageTemplate::class, 'loadTemplate');
        Hooks::addFilter('map_meta_cap', Actions\PreventDeletingCampaignPage::class, '__invoke', 10, 4);

        // notices
        add_action('wp_ajax_givewp_campaign_interaction_notice', static function () {
            add_user_meta(get_current_user_id(), 'givewp_show_campaign_interaction_notice', time(), true);
        });
    }

    /**
     * @unreleased
     */
    private function registerMenus()
    {
        Hooks::addAction('admin_menu', CampaignsAdminPage::class, 'addCampaignsSubmenuPage', 999);
    }

    /**
     * @unreleased
     */
    private function replaceGiveFormsCptLabels()
    {
        Hooks::addFilter('give_forms_labels', ReplaceGiveFormsCptLabels::class);
    }

    private function setupCampaignPages()
    {
        Hooks::addAction('enqueue_block_editor_assets', Actions\EnqueueCampaignPageEditorAssets::class);
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

        Hooks::addAction('admin_init', RedirectLegacyCreateFormToCreateCampaign::class);

        Hooks::addAction('save_post_give_forms', AddCampaignFormFromRequest::class, 'optionBasedFormEditor', 10, 3);
        Hooks::addAction('givewp_donation_form_created', AddCampaignFormFromRequest::class, 'visualFormBuilder');
        Hooks::addAction('givewp_campaign_created', CreateDefaultCampaignForm::class);
        Hooks::addAction('givewp_campaign_created', CreateCampaignPage::class);
    }

    /**
     * @unreleased
     */
    private function registerCampaignBlocks()
    {
        Hooks::addAction('rest_api_init', Actions\RegisterCampaignIdRestField::class);
        Hooks::addAction('init', Actions\RegisterCampaignBlocks::class);
        Hooks::addAction('enqueue_block_editor_assets', Actions\RegisterCampaignBlocks::class, 'loadBlockEditorAssets');
    }

    /**
     * @unreleased
     */
    private function loadCampaignOptions()
    {
        Hooks::addAction('init', LoadCampaignOptions::class);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    private function addNewBadgeToMenu(): void
    {
        (new AddNewBadgeToAdminMenuItem())();
    }

    /**
     * @unreleased
     */
    private function registerAsyncData()
    {
        // Campaigns List View Columns
        Hooks::addFilter('givewp_list_table_goal_progress_achieved_opacity', AdminCampaignListView::class,
            'maybeChangeAchievedIconOpacity');
        add_action(
            sprintf("givewp_list_table_cell_value_%s_content", GoalColumn::getId()),
            function ($value, Campaign $campaign) {
                return give(AdminCampaignListView::class)->maybeSetGoalColumnAsync($value, $campaign->id);
            },
            10,
            2
        );
        add_filter(
            sprintf("givewp_list_table_cell_value_%s_content", DonationsCountColumn::getId()),
            function ($value, Campaign $campaign) {
                return give(AdminCampaignListView::class)->maybeSetDonationsColumnAsync($value, $campaign->id);
            },
            10,
            2
        );
        add_filter(
            sprintf("givewp_list_table_cell_value_%s_content", RevenueColumn::getId()),
            function ($value, Campaign $campaign) {
                return give(AdminCampaignListView::class)->maybeSetRevenueColumnAsync($value, $campaign->id);
            },
            10,
            2
        );
    }
}
