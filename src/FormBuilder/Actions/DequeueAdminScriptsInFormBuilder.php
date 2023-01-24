<?php

namespace Give\FormBuilder\Actions;

use Give\FormBuilder\FormBuilderRouteBuilder;

/**
 * Since our form builder exists inside a WP admin page, it comes with a lot of baggage that we don't need.
 * This removes the unnecessary scripts before the page is loaded.
 *
 * @unreleased
 */
class DequeueAdminScriptsInFormBuilder
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function __invoke()
    {
        if ($this->isFormBuilderRoute()) {
            $wpScripts = wp_scripts();

            $wpScriptsRegistered = array_column($wpScripts->registered, 'handle');

            $legacyGiveScripts = [
                'give',
                'give-admin-scripts',
                'plugin-deactivation-survey-js',
                'admin-add-ons-js',
                'give-stripe-admin-js'
            ];

            $wpScripts->dequeue(
                array_merge($wpScriptsRegistered, $legacyGiveScripts)
            );

            foreach(['admin_notices', 'admin_footer', 'admin_head'] as $hook){
                remove_all_actions($hook);
            }
        }
    }

    /**
     * @unreleased
     *
     * @return bool
     */
    protected function isFormBuilderRoute(): bool
    {
        return FormBuilderRouteBuilder::isRoute();
    }
}
