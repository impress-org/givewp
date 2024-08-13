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
        give()->bind(Actions\UpdateFormTaxonomies::class, function() {
            /** @link https://github.com/impress-org/givewp/pull/7463#discussion_r1706988002 */
            return new Actions\UpdateFormTaxonomies(
                json_decode(give_clean($_POST['settings']), true)
            );
        });
    }

    /**
     * @unreleased
     */
    public function boot()
    {
        Hooks::addAction('givewp_form_builder_updated', Actions\UpdateFormTaxonomies::class);
        Hooks::addAction('givewp_form_builder_enqueue_scripts', Actions\EnqueueFormBuilderAssets::class);
    }
}
