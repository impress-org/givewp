<?php

namespace Give\FormBuilder\Routes;

use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\Helpers\Form\Utils;

/**
 * Route to edit an existing form
 */
class EditFormRoute
{
    /**
     * @since TBD Use a safe redirect and sanitize the request input.
     * @since 3.22.0 Add locale support
     * @since 3.0.3 Use isV3Form() method instead of 'post_content' to check if the form is built with Visual Builder
     * @since 3.0.0
     *
     * @return void
     */
    public function __invoke()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only edit link; it only redirects to the form builder and saves nothing.
        if (!isset($_GET['post'], $_GET['action']) || 'edit' !== sanitize_key(wp_unslash($_GET['action']))) {
            return;
        }

        // This conditional will be also triggered by WP edit bulk action
        // WP sends an array of IDs so if that is the case here, we can skip this
        if (is_array($_GET['post'])) {
            return;
        }

        $locale = isset($_GET['locale']) ? sanitize_text_field(wp_unslash($_GET['locale'])) : '';
        $post = get_post(absint($_GET['post']));
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ($post && 'give_forms' === $post->post_type && Utils::isV3Form($post->ID)) {
            wp_safe_redirect(FormBuilderRouteBuilder::makeEditFormRoute($post->ID, $locale)->getUrl());
            exit();
        }
    }
}
