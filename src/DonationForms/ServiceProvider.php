<?php

namespace Give\DonationForms;

use Exception;
use Give\DonationForms\Actions\DispatchDonateControllerDonationCreatedListeners;
use Give\DonationForms\Actions\DispatchDonateControllerSubscriptionCreatedListeners;
use Give\DonationForms\Actions\SanitizeDonationFormPreviewRequest;
use Give\DonationForms\Actions\StoreBackwardsCompatibleFormMeta;
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

        Hooks::addAction('givewp_donation_form_created', StoreBackwardsCompatibleFormMeta::class);
        Hooks::addAction('givewp_donation_form_updated', StoreBackwardsCompatibleFormMeta::class);

        $this->dispatchDonateControllerListeners();

        give(MigrationsRegister::class)->addMigrations([
            CleanMultipleSlashesOnDB::class,
            RemoveDuplicateMeta::class,
            UpdateDonationLevelsSchema::class,
        ]);
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
                    'trace' => $e->getTraceAsString()
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
