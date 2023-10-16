<?php

namespace Give\FormBuilder\Actions;

use Give\FormBuilder\FormBuilderRouteBuilder;

/**
 * Since our form builder exists inside a WP admin page, it comes with a lot of baggage that we don't need.
 * This removes the unnecessary styles before the page is loaded.
 *
 * @since 3.0.0
 */
class DequeueAdminStylesInFormBuilder
{
    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function __invoke()
    {
        if ($this->isFormBuilderRoute()) {
            $wpStyles = wp_styles();

            $legacyGiveStyles = [
                'give-styles',
                'give-admin-global-styles',
                'give-admin-styles',
                'give-admin-bar-notification',
                'give-stripe-admin-css'
            ];

            $wpStyles->dequeue($legacyGiveStyles);
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
