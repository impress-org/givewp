<?php

namespace Give\NextGen\DonationForm;

use Give\Helpers\Hooks;
use Give\NextGen\DonationForm\Actions\StoreBackwardsCompatibleFormMeta;
use Give\NextGen\DonationForm\Blocks\DonationFormBlock\Block as DonationFormBlock;
use Give\NextGen\DonationForm\Controllers\DonationFormViewController;
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
     * @unreleased
     */
    private function registerRoutes()
    {
        /**
         * @unreleased
         */
        Route::post('donate', DonateRoute::class);

        /**
         * @unreleased
         */
        Route::get('donation-form-view', static function (array $request) {
            $routeData = DonationFormViewRouteData::fromRequest($request);

            return give(DonationFormViewController::class)->show($routeData);
        });


        /**
         * @unreleased
         */
        Route::post('donation-form-view-preview', static function (array $request) {
            $routeData = DonationFormPreviewRouteData::fromRequest($request);

            return give(DonationFormViewController::class)->preview($routeData);
        });
    }
}
