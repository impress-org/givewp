<?php

namespace Give\DonationForms;

use Exception;
use Give\DonationForms\Actions\DispatchDonateControllerDonationCreatedListeners;
use Give\DonationForms\Actions\DispatchDonateControllerSubscriptionCreatedListeners;
use Give\DonationForms\Actions\SanitizeDonationFormPreviewRequest;
use Give\DonationForms\Actions\StoreBackwardsCompatibleFormMeta;
use Give\DonationForms\AsyncData\Actions\GetAsyncFormDataForListView;
use Give\DonationForms\AsyncData\Actions\LoadAsyncDataAssets;
use Give\DonationForms\AsyncData\AdminFormListView\AdminFormListView;
use Give\DonationForms\AsyncData\FormGrid\FormGridView;
use Give\DonationForms\Blocks\DonationFormBlock\Block as DonationFormBlock;
use Give\DonationForms\Controllers\DonationConfirmationReceiptViewController;
use Give\DonationForms\Controllers\DonationFormViewController;
use Give\DonationForms\DataTransferObjects\DonationConfirmationReceiptViewRouteData;
use Give\DonationForms\DataTransferObjects\DonationFormPreviewRouteData;
use Give\DonationForms\DataTransferObjects\DonationFormViewRouteData;
use Give\DonationForms\FormDesigns\ClassicFormDesign\ClassicFormDesign;
use Give\DonationForms\FormDesigns\MultiStepFormDesign\MultiStepFormDesign;
use Give\DonationForms\FormDesigns\TwoPanelStepsFormLayout\TwoPanelStepsFormLayout;
use Give\DonationForms\FormPage\TemplateHandler;
use Give\DonationForms\Migrations\CleanMultipleSlashesOnDB;
use Give\DonationForms\Migrations\RemoveDuplicateMeta;
use Give\DonationForms\Migrations\UpdateDonationLevelsSchema;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\Routes\AuthenticationRoute;
use Give\DonationForms\Routes\DonateRoute;
use Give\DonationForms\Routes\ValidationRoute;
use Give\DonationForms\Shortcodes\GiveFormShortcode;
use Give\DonationForms\V2\ListTable\Columns\DonationCountColumn;
use Give\DonationForms\V2\ListTable\Columns\DonationRevenueColumn;
use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Framework\Routes\Route;
use Give\Helpers\Hooks;
use Give\Log\Log;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    /*
     * @inheritdoc
     */
    public function register()
    {
        give()->singleton('forms', DonationFormRepository::class);

        give()->singleton(TemplateHandler::class, function () {
            global $post;

            return new TemplateHandler(
                $post,
                GIVE_PLUGIN_DIR . 'src/DonationForms/FormPage/templates/form-single.php'
            );
        });
    }

    /*
     * @inheritdoc
     */
    public function boot()
    {
        if (function_exists('register_block_type')) {
            Hooks::addAction('init', DonationFormBlock::class, 'register');
        }

        $this->registerRoutes();
        $this->registerFormDesigns();
        $this->registerSingleFormPage();
        $this->registerShortcodes();
        $this->registerPostStatus();
        $this->registerAddFormSubmenuLink();

        Hooks::addAction('givewp_donation_form_created', StoreBackwardsCompatibleFormMeta::class);
        Hooks::addAction('givewp_donation_form_updated', StoreBackwardsCompatibleFormMeta::class);

        $this->dispatchDonateControllerListeners();

        give(MigrationsRegister::class)->addMigrations([
            CleanMultipleSlashesOnDB::class,
            RemoveDuplicateMeta::class,
            UpdateDonationLevelsSchema::class,
        ]);

        $this->registerAsyncData();
    }

    /**
     * @unreleased
     */
    private function registerAsyncData()
    {

        // Async ajax request
        Hooks::addAction('wp_ajax_givewp_get_form_async_data_for_list_view', GetAsyncFormDataForListView::class);
        Hooks::addAction('wp_ajax_nopriv_givewp_get_form_async_data_for_list_view', GetAsyncFormDataForListView::class);

        // Filter from give_goal_progress_stats() function which is used by the admin form list views and form grid view
        Hooks::addFilter('give_goal_amount_raised_output', AdminFormListView::class, 'maybeChangeAmountRaisedOutput',1,2);

        // Form Grid
        add_filter('give_form_grid_goal_progress_stats_use_placeholder', function ($usePlaceholder) {
            $usePlaceholder = give(FormGridView::class)->maybeUsePlaceholderOnGoalAmountRaised($usePlaceholder);

            if ($usePlaceholder) {
                // Load assets on form grid pages
                Hooks::addAction('wp_enqueue_scripts', LoadAsyncDataAssets::class);
                Hooks::addAction('admin_enqueue_scripts', LoadAsyncDataAssets::class);
            }

            return $usePlaceholder;
        });
        Hooks::addFilter('give_form_grid_progress_bar_amount_raised_value', FormGridView::class, 'maybeSetProgressBarAmountRaisedAsync',10,2);
        Hooks::addFilter('give_form_grid_progress_bar_donations_count_value', FormGridView::class, 'maybeSetProgressBarDonationsCountAsync',10,2);

        // Load assets on the admin list form pages and pages with the WordPress clock editor enable
        $isAdminListFormPage = isset($_GET['post_type']) && 'give_forms' === $_GET['post_type'];
        if ($isAdminListFormPage) {
            Hooks::addAction('admin_enqueue_scripts', LoadAsyncDataAssets::class);
        }

        // Legacy Admin Form List View Columns
        Hooks::addFilter('give_admin_goal_progress_achieved_opacity', AdminFormListView::class, 'maybeChangeAchievedIconOpacity');
        Hooks::addFilter('give_admin_list_form_view_goal_progress_stats_use_placeholder', AdminFormListView::class, 'maybeUsePlaceholderOnGoalAmountRaised');
        Hooks::addFilter('give_admin_form_list_view_donations_count_column_value', AdminFormListView::class, 'maybeSetDonationsColumnAsync',10,2);
        Hooks::addFilter('give_admin_form_list_view_revenue_column_value', AdminFormListView::class, 'maybeSetRevenueColumnAsync',10,2);

        // Admin Form List View Columns
        Hooks::addFilter('givewp_list_table_goal_progress_achieved_opacity', AdminFormListView::class, 'maybeChangeAchievedIconOpacity');
        Hooks::addFilter('givewp_admin_list_form_view_goal_progress_stats_use_placeholder', AdminFormListView::class, 'maybeUsePlaceholderOnGoalAmountRaised');
        add_filter(
            sprintf("givewp_list_table_cell_value_%s_content", DonationCountColumn::getId()),
            function ($value, DonationForm $form){
                return give(AdminFormListView::class)->maybeSetDonationsColumnAsync($value, $form->id);
            },
            10,
            2
        );
        add_filter(
            sprintf("givewp_list_table_cell_value_%s_content", DonationRevenueColumn::getId()),
            function ($value, DonationForm $form){
                return give(AdminFormListView::class)->maybeSetRevenueColumnAsync($value, $form->id);
            },
            10,
            2
        );
    }

    /**
     * @unreleased
     */
    private function registerAddFormSubmenuLink()
    {
        Hooks::addAction('admin_menu', DonationFormsAdminPage::class, 'addFormSubmenuLink', 999);
    }

    /**
     * @since 3.0.0
     */
    private function registerRoutes()
    {
        /**
         * @since 3.0.0
         */
        Route::post('donate', DonateRoute::class);

        /**
         * @since 3.0.0
         */
        Route::post('validate', ValidationRoute::class);

        /**
         * @since 3.0.0
         */
        Route::post('authenticate', AuthenticationRoute::class);

        /**
         * @since 3.0.0
         */
        Route::get('donation-form-view', static function (array $request) {
            ini_set('display_errors', 0);
            $routeData = DonationFormViewRouteData::fromRequest($request);

            return give(DonationFormViewController::class)->show($routeData);
        });

        /**
         * @since 3.0.0
         */
        Route::get('donation-confirmation-receipt-view', static function (array $request) {
            ini_set('display_errors', 0);
            $routeData = DonationConfirmationReceiptViewRouteData::fromRequest($request);

            return give(DonationConfirmationReceiptViewController::class)->show($routeData);
        });

        /**
         * @since 3.0.0
         */
        Route::post('donation-form-view-preview', static function () {
            ini_set('display_errors', 0);
            $requestData = (new SanitizeDonationFormPreviewRequest())($_REQUEST);
            $routeData = DonationFormPreviewRouteData::fromRequest($requestData);

            return give(DonationFormViewController::class)->preview($routeData);
        });
    }

    /**
     * @since 3.0.0
     */
    private function dispatchDonateControllerListeners()
    {
        Hooks::addAction(
            'givewp_donate_controller_donation_created',
            DispatchDonateControllerDonationCreatedListeners::class,
            '__invoke',
            10,
            3
        );

        Hooks::addAction(
            'givewp_donate_controller_subscription_created',
            DispatchDonateControllerSubscriptionCreatedListeners::class,
            '__invoke',
            10,
            3
        );
    }

    /**
     * @since 3.0.0
     */
    private function registerFormDesigns()
    {
        add_action('givewp_register_form_design', static function (FormDesignRegistrar $formDesignRegistrar) {
            try {
                $formDesignRegistrar->registerDesign(ClassicFormDesign::class);
                $formDesignRegistrar->registerDesign(MultiStepFormDesign::class);
                $formDesignRegistrar->registerDesign(TwoPanelStepsFormLayout::class);
            } catch (Exception $e) {
                Log::error('Error registering form designs', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });
    }

    /**
     * @since 3.0.0
     */
    protected function registerSingleFormPage()
    {
        Hooks::addFilter('template_include', TemplateHandler::class, 'handle', 11);
    }

    /**
     * @since 3.0.0
     */
    protected function registerShortcodes()
    {
        Hooks::addFilter('givewp_form_shortcode_output', GiveFormShortcode::class, '__invoke', 10, 2);
    }

    /**
     * @since 3.0.0
     */
    protected function registerPostStatus()
    {
        add_action('init', static function () {
            register_post_status(DonationFormStatus::UPGRADED);
        });
    }
}
