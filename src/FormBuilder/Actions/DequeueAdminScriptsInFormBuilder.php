<?php

namespace Give\FormBuilder\Actions;

use Give\FormBuilder\FormBuilderRouteBuilder;

/**
 * Since our form builder exists inside a WP admin page, it comes with a lot of baggage that we don't need.
 * This removes the unnecessary scripts before the page is loaded.
 *
 * @since 3.0.0
 */
class DequeueAdminScriptsInFormBuilder
{
    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function __invoke()
    {
        if ($this->isFormBuilderRoute()) {
            $wpScripts = wp_scripts();

            $legacyGiveScripts = [
                'give',
                'give-admin-scripts',
                'plugin-deactivation-survey-js',
                'admin-add-ons-js',
                'give-stripe-admin-js'
            ];

            $wpScripts->dequeue($legacyGiveScripts);
        }
    }

    /**
     * @since 3.0.0
     *
     * @return bool
     */
    protected function isFormBuilderRoute(): bool
    {
        return FormBuilderRouteBuilder::isRoute();
    }
}
