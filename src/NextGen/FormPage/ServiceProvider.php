<?php

namespace Give\NextGen\FormPage;

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
        give()->singleton(TemplateHandler::class, function () {
            global $post;

            return new TemplateHandler(
                $post,
                plugin_dir_path(__FILE__) . 'templates/next-gen-form-single.php'
            );
        });
    }

    /*
     * @inheritdoc
     */
    public function boot()
    {
        Hooks::addFilter('template_include', TemplateHandler::class, 'handle', 11);
    }
}
