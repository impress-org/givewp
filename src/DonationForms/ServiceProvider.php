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
use Give\DonationForms\FormDesigns\DeveloperFormDesign\DeveloperFormDesign;
use Give\DonationForms\FormDesigns\MultiStepFormDesign\MultiStepFormDesign;
use Give\DonationForms\FormPage\TemplateHandler;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\Routes\AuthenticationRoute;
use Give\DonationForms\Routes\DonateRoute;
use Give\DonationForms\Routes\ValidationRoute;
use Give\DonationForms\Shortcodes\GiveFormShortcode;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
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
                GIVE_NEXT_GEN_DIR . 'src/DonationForms/FormPage/templates/form-single.php'
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

        Hooks::addAction('givewp_donation_form_created', StoreBackwardsCompatibleFormMeta::class);
        Hooks::addAction('givewp_donation_form_updated', StoreBackwardsCompatibleFormMeta::class);

        $this->dispatchDonateControllerListeners();
    }

    /**
     * @since 0.1.0
     */
    private function registerRoutes()
    {
        /**
         * @since 0.1.0
         */
        Route::post('donate', DonateRoute::class);

        /**
         * @since 0.4.0
         */
        Route::post('validate', ValidationRoute::class);

        /**
         * @since 0.4.0
         */
        Route::post('authenticate', AuthenticationRoute::class);

        /**
         * @since 0.1.0
         */
        Route::get('donation-form-view', static function (array $request) {
            $routeData = DonationFormViewRouteData::fromRequest($request);

            return give(DonationFormViewController::class)->show($routeData);
        });

        /**
         * @since 0.1.0
         */
        Route::get('donation-confirmation-receipt-view', static function (array $request) {
            $routeData = DonationConfirmationReceiptViewRouteData::fromRequest($request);

            return give(DonationConfirmationReceiptViewController::class)->show($routeData);
        });


        /**
         * @since 0.1.0
         */
        Route::post('donation-form-view-preview', static function () {
            $requestData = (new SanitizeDonationFormPreviewRequest())($_REQUEST);
            $routeData = DonationFormPreviewRouteData::fromRequest($requestData);

            return give(DonationFormViewController::class)->preview($routeData);
        });
    }

    /**
     * @since 0.3.0
     */
    private function dispatchDonateControllerListeners()
    {
        Hooks::addAction(
            'givewp_donate_controller_donation_created',
            DispatchDonateControllerDonationCreatedListeners::class,
            '__invoke',
            10,
            2
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
     * @since 0.1.0
     */
    private function registerFormDesigns()
    {
        add_action('givewp_register_form_design', static function (FormDesignRegistrar $formDesignRegistrar) {
            try {
                $formDesignRegistrar->registerDesign(ClassicFormDesign::class);
                $formDesignRegistrar->registerDesign(DeveloperFormDesign::class);
                $formDesignRegistrar->registerDesign(MultiStepFormDesign::class);
            } catch (Exception $e) {
                Log::error('Error registering form designs', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    }

    /**
     * @since 0.4.0
     */
    protected function registerSingleFormPage()
    {
        Hooks::addFilter('template_include', TemplateHandler::class, 'handle', 11);
    }

    /**
     * @since 0.5.0
     */
    protected function registerShortcodes()
    {
        Hooks::addFilter('givewp_form_shortcode_output', GiveFormShortcode::class, '__invoke', 10, 2);
    }
}
