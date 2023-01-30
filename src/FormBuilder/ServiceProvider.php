<?php

namespace Give\FormBuilder;

use Give\FormBuilder\Actions\DequeueAdminScriptsInFormBuilder;
use Give\FormBuilder\Actions\DequeueAdminStylesInFormBuilder;
use Give\FormBuilder\Routes\CreateFormRoute;
use Give\FormBuilder\Routes\EditFormRoute;
use Give\FormBuilder\Routes\RegisterFormBuilderPageRoute;
use Give\FormBuilder\Routes\RegisterFormBuilderRestRoutes;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 0.1.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('rest_api_init', RegisterFormBuilderRestRoutes::class);

        Hooks::addAction('admin_init', CreateFormRoute::class);

        Hooks::addAction('admin_init', EditFormRoute::class);

        Hooks::addAction('admin_menu', RegisterFormBuilderPageRoute::class);

        Hooks::addAction('admin_print_scripts', DequeueAdminScriptsInFormBuilder::class);

        Hooks::addAction('admin_print_styles', DequeueAdminStylesInFormBuilder::class);

        /** Integrates the "Add Next Gen Form" button with the Donation Forms table. */
        add_action('admin_enqueue_scripts', static function () {
            wp_localize_script('give-admin-donation-forms', 'GiveNextGen', [
                'newFormUrl' => FormBuilderRouteBuilder::makeCreateFormRoute()->getUrl(),
            ]);
        });
    }
}
