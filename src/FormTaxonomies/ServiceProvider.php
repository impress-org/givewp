<?php

namespace Give\FormTaxonomies;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 3.16.0
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @since 3.16.0
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
     * @since 3.16.0
     */
    public function boot()
    {
        Hooks::addAction('givewp_form_builder_updated', Actions\UpdateFormTaxonomies::class, '__invoke', 10, 2);
        Hooks::addAction('givewp_form_builder_enqueue_scripts', Actions\EnqueueFormBuilderAssets::class);
    }
}
