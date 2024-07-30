<?php

namespace Give\FormTaxonomies;

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
        // This section
    }

    /**
     * @unreleased
     */
    public function boot()
    {
        add_action('givewp_form_builder_updated', give(Actions\UpdateFormTaxonomies::class));
        add_action('givewp_form_builder_enqueue_scripts', give(Actions\EnqueueFormBuilderAssets::class));
    }
}
