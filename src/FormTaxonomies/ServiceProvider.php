<?php

namespace Give\FormTaxonomies;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @unreleased
     */
    public function register()
    {
        give()->bind(Actions\EnqueueFormBuilderAssets::class, function() {
            $formId = absint($_GET['donationFormID'] ?? 0);
            return new Actions\EnqueueFormBuilderAssets(
                new ViewModels\FormTaxonomyViewModel($formId, give_get_settings())
            );
        });
    }

    /**
     * @unreleased
     */
    public function boot()
    {
        Hooks::addAction('givewp_form_builder_updated', Actions\UpdateFormTaxonomies::class, '__invoke', 10, 2);
        Hooks::addAction('givewp_form_builder_enqueue_scripts', Actions\EnqueueFormBuilderAssets::class);
    }
}
