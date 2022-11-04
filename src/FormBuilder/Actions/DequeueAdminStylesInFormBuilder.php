<?php

namespace Give\FormBuilder\Actions;

/**
 * Since our form builder exists inside a WP admin page, it comes with a lot of baggage that we don't need.
 * This removes the unnecessary styles before the page is loaded.
 *
 * @unreleased
 */
class DequeueAdminStylesInFormBuilder
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function __invoke()
    {
        if ($this->isFormBuilderRoute()) {
            $wpStyles = wp_styles();
            $wpStylesRegistered = array_column($wpStyles->registered, 'handle');
            $legacyGiveStyles = [
                'give-styles',
                'give-admin-global-styles',
                'give-admin-styles',
                'give-admin-bar-notification',
                'give-stripe-admin-css'
            ];

            $wpStyles->dequeue(array_merge($wpStylesRegistered, $legacyGiveStyles));
        }
    }

    /**
     * @unreleased
     *
     * @return bool
     */
    protected function isFormBuilderRoute(): bool
    {
        return isset($_GET['post_type'], $_GET['page']) && $_GET['post_type'] === 'give_forms' && $_GET['page'] === 'campaign-builder';
    }
}
