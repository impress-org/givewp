<?php

namespace Give\NextGen\DonationForm;

use Give\Helpers\Hooks;
use Give\NextGen\DonationForm\Actions\StoreBackwardsCompatibleFormMeta;
use Give\NextGen\DonationForm\Blocks\DonationFormBlock\Block as DonationFormBlock;
use Give\NextGen\DonationForm\Controllers\DonationConfirmationReceiptViewController;
use Give\NextGen\DonationForm\Controllers\DonationFormViewController;
use Give\NextGen\DonationForm\DataTransferObjects\DonationConfirmationReceiptViewRouteData;
use Give\NextGen\DonationForm\DataTransferObjects\DonationFormPreviewRouteData;
use Give\NextGen\DonationForm\DataTransferObjects\DonationFormViewRouteData;
use Give\NextGen\DonationForm\Routes\DonateRoute;
use Give\NextGen\Framework\Routes\Route;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    /*
     * @inheritdoc
     */
    public function register()
    {
        // TODO: Implement register() method.
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

        Hooks::addAction('givewp_donation_form_created', StoreBackwardsCompatibleFormMeta::class);
        Hooks::addAction('givewp_donation_form_updated', StoreBackwardsCompatibleFormMeta::class);
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
        Route::post('donation-form-view-preview', static function (array $request) {
            $routeData = DonationFormPreviewRouteData::fromRequest($request);

            return give(DonationFormViewController::class)->preview($routeData);
        });
    }
}
